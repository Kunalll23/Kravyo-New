"""
Kravyo — Flask AI Recommendation API Server (app.py)
=====================================================

Endpoints:
    GET  /recommend?user_id={id}   → JSON recommendation list
    GET  /health                   → JSON server status

Run:
    python python_ai/app.py
    (or: python app.py from inside the python_ai/ folder)

Server listens on:
    http://127.0.0.1:5000/
"""

from flask import Flask, jsonify, request
from recommender import get_recommendations, load_config, save_config

app = Flask(__name__)


# ─── Health Check ──────────────────────────────────────────────────────────

@app.route("/health", methods=["GET"])
def health():
    """Simple liveness probe used by PHP to check if the server is online."""
    return jsonify({"status": "ok", "service": "Kravyo AI Recommendation Server"}), 200


# ─── Config Read ───────────────────────────────────────────────────────────

@app.route("/config", methods=["GET"])
def get_config():
    """Return current AI configuration values. Used by the Admin dashboard."""
    cfg = load_config()
    return jsonify({"success": True, "config": cfg}), 200


# ─── Config Write ──────────────────────────────────────────────────────────

@app.route("/config", methods=["POST"])
def update_config():
    """
    Update AI configuration parameters.
    Expects JSON body: {"similarity_weight": float, "popularity_weight": float, "default_limit": int}
    All values are validated server-side before writing.
    """
    data = request.get_json(force=True, silent=True)
    if not data:
        return jsonify({"success": False, "error": "Invalid JSON body"}), 400

    # --- Validate similarity_weight ---
    try:
        sw = float(data.get("similarity_weight", -1))
    except (ValueError, TypeError):
        return jsonify({"success": False, "error": "similarity_weight must be a number"}), 400
    if not (0.0 <= sw <= 1.0):
        return jsonify({"success": False, "error": "similarity_weight must be between 0 and 1"}), 400

    # --- Validate popularity_weight ---
    try:
        pw = float(data.get("popularity_weight", -1))
    except (ValueError, TypeError):
        return jsonify({"success": False, "error": "popularity_weight must be a number"}), 400
    if not (0.0 <= pw <= 1.0):
        return jsonify({"success": False, "error": "popularity_weight must be between 0 and 1"}), 400

    # --- Validate they sum to 1 (allow ±0.01 float tolerance) ---
    if abs(sw + pw - 1.0) > 0.01:
        return jsonify({"success": False, "error": "similarity_weight + popularity_weight must equal 1.0"}), 400

    # --- Validate default_limit ---
    try:
        lm = int(data.get("default_limit", -1))
    except (ValueError, TypeError):
        return jsonify({"success": False, "error": "default_limit must be an integer"}), 400
    if not (1 <= lm <= 20):
        return jsonify({"success": False, "error": "default_limit must be between 1 and 20"}), 400

    # --- Write to disk ---
    new_cfg = {"similarity_weight": round(sw, 4), "popularity_weight": round(pw, 4), "default_limit": lm}
    if save_config(new_cfg):
        return jsonify({"success": True, "config": new_cfg}), 200
    else:
        return jsonify({"success": False, "error": "Failed to write config file"}), 500


# ─── Main Recommendation Endpoint ──────────────────────────────────────────

@app.route("/recommend", methods=["GET"])
def recommend():
    """
    GET /recommend?user_id=123&limit=8

    Returns:
    {
        "success": true,
        "user_id": 123,
        "algorithm": "content_based_filtering_tfidf_cosine",
        "recommendations": [
            {
                "menu_item_id": 15,
                "similarity_score": 0.82,
                "popularity_score": 0.45,
                "final_score": 0.75
            },
            ...
        ]
    }

    Error responses always have "success": false and "error": "<message>".
    Stack traces and DB credentials are NEVER exposed.
    """

    # ── Validate user_id ────────────────────────────────────────────────────
    raw_user_id = request.args.get("user_id", "").strip()

    if not raw_user_id:
        return jsonify({
            "success": False,
            "error": "Missing required parameter: user_id"
        }), 400

    if not raw_user_id.lstrip("-").isdigit():
        return jsonify({
            "success": False,
            "error": "Invalid user_id: must be a positive integer"
        }), 400

    user_id = int(raw_user_id)
    if user_id <= 0:
        return jsonify({
            "success": False,
            "error": "Invalid user_id: must be greater than 0"
        }), 400

    # ── Validate optional limit ─────────────────────────────────────────────
    raw_limit = request.args.get("limit", "8").strip()
    try:
        limit = max(1, min(32, int(raw_limit)))  # clamp between 1 and 32
    except ValueError:
        limit = 8

    # ── Run ML recommendation ───────────────────────────────────────────────
    try:
        recommendations = get_recommendations(user_id, limit)
    except Exception as exc:
        # Log internally but NEVER expose details to the caller
        app.logger.error(f"Recommendation error for user {user_id}: {exc}")
        return jsonify({
            "success": False,
            "error": "Recommendation engine error. Please try again later."
        }), 500

    return jsonify({
        "success": True,
        "user_id": user_id,
        "algorithm": "content_based_filtering_tfidf_cosine",
        "recommendations": recommendations,
    }), 200


# ─── Entry Point ────────────────────────────────────────────────────────────

if __name__ == "__main__":
    print("=" * 55)
    print("  Kravyo AI Recommendation Server")
    print("  http://127.0.0.1:5000/")
    print("  Endpoints: /recommend?user_id=N  |  /health")
    print("=" * 55)
    app.run(host="127.0.0.1", port=5000, debug=False)
