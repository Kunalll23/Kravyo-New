<?php
$filePath = __DIR__ . '/../ROADMAP.md';
$content = file_get_contents($filePath);

$isCrlf = strpos($content, "\r\n") !== false;
$eol = $isCrlf ? "\r\n" : "\n";

// 1. In Phase Overview:
$search1 = 'Phase 10: AI Recommendation Engine Integration & Final Polish (COMPLETED ✅)' . $eol . '```';
$replace1 = 'Phase 10: AI Recommendation Engine Integration & Final Polish (COMPLETED ✅)' . $eol .
           'Phase 11: Nearby Cloud Kitchens Interactive Map System (COMPLETED ✅)' . $eol . '```';

// 2. In Detailed Phase Breakdown:
$search2 = '- [x] **Documentation & Presentation:** ROADMAP updated; `user_preferences` DB migration created; `phase10_migration.sql` ready to run';
$replace2 = '- [x] **Documentation & Presentation:** ROADMAP updated; `user_preferences` DB migration created; `phase10_migration.sql` ready to run' . $eol . $eol .
'---' . $eol . $eol .
'### Phase 11: Nearby Cloud Kitchens Interactive Map System (Completed ✅)' . $eol .
'- [x] **Geospatial Schema & Indexing:** Added `latitude` and `longitude` fields to `kitchens` table with `idx_kitchens_geo` composite index (`nearby_kitchens_migration.sql`)' . $eol .
'- [x] **Haversine Distance Engine:** Backend SQL bounding-box optimization + Haversine distance formula in `Kitchen::findNearby()`' . $eol .
'- [x] **Interactive Customer Map:** Leaflet.js and OpenStreetMap tile integration with custom animated markers (pulsing customer pin, Kravyo chef pins)' . $eol .
'- [x] **Dynamic Radius & City Filtering:** Real-time radius selector (2km, 5km, 10km, 20km, 50km), city quick-jump, and open-now toggles' . $eol .
'- [x] **Side-by-Side Responsive UI:** Synchronized scrollable kitchen card list and map on desktop; stacked layout on mobile' . $eol .
'- [x] **Chef Profile Location Picker:** Interactive draggable pin map picker and "Detect My Location" GPS button in Chef Profile' . $eol .
'- [x] **Location Privacy & Graceful Fallback:** Permission-denied fallback banner with manual city selection; zero continuous background tracking';

if ($isCrlf) {
    $search1 = str_replace("\n", "\r\n", $search1);
    $replace1 = str_replace("\n", "\r\n", $replace1);
    $search2 = str_replace("\n", "\r\n", $search2);
    $replace2 = str_replace("\n", "\r\n", $replace2);
}

$content = str_replace($search1, $replace1, $content);
$content = str_replace($search2, $replace2, $content);

file_put_contents($filePath, $content);
echo "SUCCESS: ROADMAP.md updated.\n";
