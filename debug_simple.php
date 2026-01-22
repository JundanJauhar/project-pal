<?php
// Simple debug
use App\Models\User;
use App\Models\EvatekItem;

echo "=== USERS ===\n";
// Just get users and print their raw attributes
foreach(User::all() as $u) {
    echo "ID: {$u->user_id}, Name: {$u->name}, Roles: '{$u->roles}'\n";
}

echo "\n=== EVATEK ITEMS ===\n";
foreach(EvatekItem::with('latestRevision')->get() as $ev) {
    if ($ev->status == 'on_progress') {
        echo "ID: {$ev->evatek_id} | Status: {$ev->status}\n";
        $rev = $ev->latestRevision;
        if ($rev) {
            echo "   Rev: {$rev->revision_code} | Status: '{$rev->status}' | VendorLink: " . ($rev->vendor_link ? 'SET' : 'NULL') . "\n";
        } else {
            echo "   Rev: NULL\n";
        }
    }
}
