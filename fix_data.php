<?php
use App\Models\EvatekItem;
use App\Models\EvatekRevision;

$items = EvatekItem::doesntHave('revisions')->get();
echo "Found " . $items->count() . " orphaned items.\n";

foreach($items as $ev) {
    echo "Creating R0 for Evatek ID {$ev->evatek_id}...\n";
    EvatekRevision::create([
        'evatek_id' => $ev->evatek_id,
        'revision_code' => 'R0',
        'vendor_link' => null,
        'design_link' => null,
        'status' => 'pending',
        'date' => now(),
    ]);
}

// Update first item to have a vendor link to test notification
$first = EvatekItem::first();
if ($first) {
    echo "Simulating Vendor Upload for Item {$first->evatek_id}...\n";
    $rev = $first->latestRevision;
    $rev->vendor_link = "http://example.com/dummy-file.pdf";
    $rev->save();
    // Ensure evatek status is on_progress or pending
    // $first->status = 'on_progress'; 
    // $first->save();
}
