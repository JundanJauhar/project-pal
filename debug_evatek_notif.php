<?php
use App\Models\User;
use App\Models\EvatekItem;
use App\Models\EvatekRevision;
use Illuminate\Support\Facades\Auth;

// We need to simulate the user login or just get the user by email/name if we knew it.
// Since we are in CLI, we can't 'get current user'.
// But we can list users with 'desain' role or similar.

echo "=== USERS WITH 'desain' ROLE ===\n";
$users = User::where('roles', 'like', '%desain%')->get();
foreach ($users as $u) {
    echo "ID: {$u->user_id}, Name: {$u->name}, Roles: {$u->roles}, Division: " . ($u->division->name ?? 'N/A') . "\n";
}

echo "\n=== EVATEK ITEMS 'On_progress' ===\n";
$items = EvatekItem::with(['item', 'latestRevision'])->where('status', 'on_progress')->get();

foreach ($items as $ev) {
    echo "Evatek ID: {$ev->evatek_id}\n";
    echo "Item: " . ($ev->item->item_name ?? 'N/A') . "\n";
    echo "Status: {$ev->status}\n";
    
    $rev = $ev->latestRevision;
    if ($rev) {
        echo "  Latest Rev: {$rev->revision_code}\n";
        echo "  Rev Status: {$rev->status}\n";
        echo "  Vendor Link: " . ($rev->vendor_link ? 'YES (' . substr($rev->vendor_link, 0, 20) . '...)' : 'NO (NULL)') . "\n";
    } else {
        echo "  Latest Rev: NONE\n";
    }
    echo "--------------------------\n";
}
