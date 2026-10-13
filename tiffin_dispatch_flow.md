# Kravyo Tiffin Subscription Delivery Flow

This document outlines the end-to-end "Ultra Processing" lifecycle of a Tiffin Subscription meal in the Kravyo ecosystem, showing how the automated generation connects with the live Dispatch system.

## Complete Process Flow

```mermaid
sequenceDiagram
    autonumber
    actor C as Customer
    participant DB as Kravyo DB
    actor K as Chef (Kitchen)
    participant D as DispatchService
    actor R as Delivery Rider
    
    %% Setup
    C->>DB: Subscribes to Tiffin Plan (e.g., 2 Meals/Day)
    K->>DB: Approves Subscription
    
    %% Daily Auto-Generation
    Note over DB,K: 1. Auto-Generation (Daily)
    DB-->>K: Auto-generates "Lunch" & "Dinner" pending deliveries
    K->>K: Views "Today's Tiffin Deliveries" Dashboard
    
    %% Preparation & Dispatch
    Note over K,D: 2. Preparation & Dispatch
    K->>DB: Prepares Lunch Tiffin
    K->>DB: Clicks "Prepare & Dispatch Rider"
    DB->>DB: Upgrades Tiffin to Live Order (order_type='tiffin')
    DB->>D: Triggers DispatchService::broadcast()
    
    %% Delivery Partner Routing
    Note over D,R: 3. Rider Routing
    D-->>R: Broadcasts Delivery Offer to all riders in City
    R->>D: Accepts Tiffin Offer
    R->>K: Navigates to Kitchen & Picks up Tiffin
    R->>R: Marks "Out for Delivery"
    R-->>C: Live Map Tracking Starts
    
    %% Dropoff
    Note over R,C: 4. Verification & Dropoff
    R->>C: Arrives at Customer
    C->>R: Provides OTP
    R->>DB: Enters OTP & Marks "Delivered"
    DB->>DB: Updates Order to "delivered"
    DB->>DB: Updates Subscription Delivery to "delivered"
    DB-->>C: Sends Delivery Success Notification
```

## Key Upgrades Implemented

### 1. Meal-Specific Auto-Generation
Instead of generating one generic delivery record per day, the system now automatically scans the `meals_per_day` of each subscription.
- **1 Meal/Day**: Auto-generates a `lunch` delivery.
- **2 Meals/Day**: Auto-generates `lunch` and `dinner` deliveries.
- **3 Meals/Day**: Auto-generates `breakfast`, `lunch`, and `dinner` deliveries.
The Chef's dashboard explicitly tells them which meal type they need to prepare.

### 2. Seamless Order Upgrading
When the Chef clicks **"Prepare & Dispatch Rider"** on a Tiffin delivery, Kravyo seamlessly generates a virtual live order in the `orders` table. 
- It sets `order_type = 'tiffin'`.
- It sets `total_amount = 0` (since it's prepaid via subscription).
- It dynamically generates the instructions (e.g., `TIFFIN LUNCH - No Spice`).

### 3. Integrated Live Dispatch
Once the virtual order is created, the Tiffin completely enters the standard Kravyo delivery ecosystem.
- The `DispatchService` blasts the offer to nearby riders.
- The Rider accepts, navigates to the kitchen, and picks it up.
- The Customer gets full Google Maps live tracking.
- The Rider requires an OTP from the customer to complete the dropoff.

### 4. Dual-Synchronization
When the Delivery Rider drops off the Tiffin and submits the OTP, Kravyo automatically syncs backwards and marks the original `subscription_deliveries` record as `Delivered` to keep the Chef's subscription reports accurate.
