@extends('layouts.app')

@section('title', 'Notifikasi')

@push('styles')
<style>
    /* Gmail-like Layout Styles */
    body {
        background-color: #f6f8fc;
    }
    
    .email-container {
        display: flex;
        height: calc(100vh - 100px); /* Adjust based on navbar height */
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 24px rgba(0,0,0,0.05);
        margin-top: 20px;
    }

    /* Sidebar */
    .email-sidebar {
        width: 260px;
        background: #f8f9fa; /* Light gray like Gmail sidebar */
        padding: 20px 10px;
        display: flex;
        flex-direction: column;
        border-right: 1px solid #e0e0e0;
        flex-shrink: 0;
    }

    .compose-btn {
        background: #c2e7ff;
        color: #001d35;
        border: none;
        border-radius: 16px;
        padding: 15px 24px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
        transition: box-shadow 0.2s;
        width: fit-content;
    }
    .compose-btn:hover {
        box-shadow: 0 1px 3px 0 rgba(60,64,67,0.3);
    }

    .sidebar-item {
        display: flex;
        align-items: center;
        padding: 10px 12px 10px 26px;
        border-radius: 0 16px 16px 0;
        cursor: pointer;
        color: #444746;
        font-weight: 500;
        margin-bottom: 4px;
        transition: background 0.2s;
        text-decoration: none;
        position: relative;
    }

    .sidebar-item:hover {
        background-color: #f2f2f2;
    }

    .sidebar-item.active {
        background-color: #d3e3fd;
        color: #001d35;
        font-weight: 700;
    }

    .sidebar-item i {
        margin-right: 18px;
        font-size: 18px;
    }

    .sidebar-badge {
        margin-left: auto;
        font-size: 12px;
        font-weight: 600;
    }

    /* Main Content */
    .email-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-width: 0; /* Fix flex overflow */
    }

    .email-toolbar {
        padding: 10px 20px;
        border-bottom: 1px solid #e0e0e0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #fff;
    }

    .email-list {
        flex: 1;
        overflow-y: auto;
        background: #fff;
    }

    /* Email Row */
    .email-row {
        display: flex;
        align-items: center;
        padding: 10px 20px;
        border-bottom: 1px solid #f2f2f2;
        cursor: pointer;
        transition: box-shadow 0.2s, background 0.2s;
        position: relative;
    }

    .email-row:hover {
        box-shadow: inset 1px 0 0 #dadce0, inset -1px 0 0 #dadce0, 0 1px 2px 0 rgba(60,64,67,.3), 0 1px 3px 1px rgba(60,64,67,.15);
        z-index: 1;
        background: #f8f9fa;
    }

    .email-row.unread {
        background-color: #fff;
        font-weight: 700;
    }
    
    .email-row.read {
        background-color: #f8f9fa; /* Slightly darker for read logic if desired, or keep white */
        font-weight: 400;
    }
    .email-row.read .email-sender, .email-row.read .email-subject {
        color: #5f6368;
    }
    
    .email-checkbox {
        margin-right: 15px;
    }
    
    .email-star {
        margin-right: 15px;
        color: #dadce0;
    }
    .email-star.active {
        color: #f4b400;
    }

    .email-sender {
        width: 200px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 14px;
        margin-right: 20px;
    }

    .email-content {
        flex: 1;
        display: flex;
        align-items: center;
        overflow: hidden;
    }

    .email-subject {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 14px;
    }
    
    .email-snippet {
        color: #5f6368;
        font-weight: 400;
        margin-left: 5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .email-meta {
        margin-left: auto;
        display: flex;
        align-items: center;
        font-size: 12px;
        color: #5f6368;
        padding-left: 10px;
        white-space: nowrap;
    }

    .badge-category {
        font-size: 10px;
        padding: 2px 8px;
        border-radius: 12px;
        margin-right: 10px;
        text-transform: uppercase;
        font-weight: 700;
    }

    .badge-vendor { background: #e8f0fe; color: #1967d2; }
    .badge-division { background: #fce8e6; color: #c5221f; }
    .badge-inbox { background: #e6f4ea; color: #137333; }

    /* Empty State */
    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: #5f6368;
    }
    .empty-state i {
        font-size: 48px;
        margin-bottom: 16px;
        color: #dadce0;
    }

    @media (max-width: 768px) {
        .email-sidebar {
            position: absolute;
            left: -260px;
            height: 100%;
            z-index: 100;
            transition: left 0.3s;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .email-sidebar.show {
            left: 0;
        }
        .email-sender {
            width: 140px;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="email-container">
        <!-- Sidebar -->
        <div class="email-sidebar" id="emailSidebar">
            <div class="mb-3 px-3">
                <button class="btn btn-outline-secondary d-md-none mb-3 w-100" onclick="toggleSidebar()">
                    <i class="bi bi-x-lg"></i> Close Menu
                </button>
                <div class="h5 fw-bold text-secondary mb-4 px-2">Notifikasi</div>
            </div>

            <a href="javascript:void(0)" class="sidebar-item active" onclick="filterEmails('all', this)" id="filter-all">
                <i class="bi bi-inbox-fill"></i> Kotak Masuk
                <span class="sidebar-badge" id="count-all">{{ $notifications->count() }}</span>
            </a>
            <a href="javascript:void(0)" class="sidebar-item" onclick="filterEmails('unread', this)">
                <i class="bi bi-envelope-exclamation"></i> Belum Dibaca
                <span class="sidebar-badge" id="count-unread">{{ $notifications->where('is_read', false)->count() }}</span>
            </a>
            <a href="javascript:void(0)" class="sidebar-item" onclick="filterEmails('read', this)">
                <i class="bi bi-envelope-open"></i> Sudah Dibaca
            </a>
            <a href="javascript:void(0)" class="sidebar-item" onclick="filterEmails('starred', this)">
                <i class="bi bi-star" style="color: #f4b400;"></i> Berbintang
                <span class="sidebar-badge" id="count-starred">{{ $notifications->where('is_starred', true)->count() }}</span>
            </a>
            
            <div class="mt-4 mb-2 px-4 text-xs font-weight-bold text-secondary text-uppercase" style="font-size: 11px;">Labels</div>
            
            <a href="javascript:void(0)" class="sidebar-item" onclick="filterEmails('division', this)">
                <i class="bi bi-person-workspace" style="color: #c5221f;"></i> Di Divisi
                <span class="sidebar-badge">{{ $notifications->where('category', 'division')->count() }}</span>
            </a>
            <a href="javascript:void(0)" class="sidebar-item" onclick="filterEmails('vendor', this)">
                <i class="bi bi-building" style="color: #1967d2;"></i> Di Vendor
                <span class="sidebar-badge">{{ $notifications->where('category', 'vendor')->count() }}</span>
            </a>
        </div>

        <!-- Main Area -->
        <div class="email-main">
            <!-- Toolbar -->
            <div class="email-toolbar">
                <div class="d-flex align-items-center">
                    <button class="btn btn-link text-dark d-md-none me-3" onclick="toggleSidebar()">
                        <i class="bi bi-list fs-4"></i>
                    </button>
                    <div class="input-group" style="max-width: 400px;">
                        <span class="input-group-text bg-transparent border-0"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control bg-transparent border-0" id="searchNotif" placeholder="Telusuri notifikasi..." onkeyup="searchEmails()">
                    </div>
                </div>
                <div>
                     <form action="{{ route('notifications.read-all') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-sm text-secondary" title="Tandai semua dibaca">
                            <i class="bi bi-check-all fs-5"></i>
                        </button>
                    </form>
                    <button class="btn btn-sm text-secondary ms-2" title="Refresh" onclick="window.location.reload()">
                        <i class="bi bi-arrow-clockwise fs-5"></i>
                    </button>
                </div>
            </div>

            <!-- List -->
            <div class="email-list" id="emailList">
                @if($notifications->count() > 0)
                    @foreach($notifications as $notif)
                        @php
                            $isRead = $notif->is_read ?? false;
                            $cat = $notif->category ?? 'inbox';
                            // Determine class based on category for visualization
                            $badgeClass = match($cat) {
                                'vendor' => 'badge-vendor',
                                'division' => 'badge-division',
                                default => 'badge-inbox'
                            };
                            $badgeLabel = match($cat) {
                                'vendor' => 'Vendor',
                                'division' => 'Action Needed',
                                default => 'Inbox'
                            };
                            
                            $rowClass = $isRead ? 'read' : 'unread';
                            $isStarred = $notif->is_starred ?? false;
                            $starClass = $isStarred ? 'active' : '';
                            $starIcon = $isStarred ? 'bi-star-fill' : 'bi-star';
                        @endphp
                        
                        <div class="email-row {{ $rowClass }}" 
                             data-category="{{ $cat }}" 
                             data-read="{{ $isRead ? 'true' : 'false' }}"
                             data-starred="{{ $isStarred ? 'true' : 'false' }}"
                             data-id="{{ $notif->id }}"
                             data-link="{{ $notif->link ?? '#' }}"
                             onclick="openNotification(this)">
                            
                            <div class="email-checkbox" onclick="event.stopPropagation()">
                                <input type="checkbox" class="form-check-input">
                            </div>
                            <div class="email-star {{ $starClass }}" onclick="toggleStar(this, '{{ $notif->id }}'); event.stopPropagation()">
                                <i class="bi {{ $starIcon }}"></i>
                            </div>
                            
                            <div class="email-sender">
                                {{ $notif->title }}
                            </div>
                            
                            <div class="email-content">
                                <span class="{{ $badgeClass }} badge-category">{{ $badgeLabel }}</span>
                                <span class="email-subject">{{ $notif->message }}</span>
                                <span class="email-snippet"> - Click to view details</span>
                            </div>
                            
                            <div class="email-meta">
                                @if(isset($notif->date))
                                    {{ \Carbon\Carbon::parse($notif->date)->format('M d') }}
                                @else
                                    {{ \Carbon\Carbon::parse($notif->created_at)->format('M d') }}
                                @endif
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <h5>Your inbox is empty</h5>
                        <p class="text-muted">No new notifications</p>
                    </div>
                @endif
                
                <!-- Hidden Empty State for Filtering -->
                <div class="empty-state d-none" id="filterEmptyState">
                    <i class="bi bi-search"></i>
                    <h5>No messages found</h5>
                    <p class="text-muted">Try a different filter</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleSidebar() {
        document.getElementById('emailSidebar').classList.toggle('show');
    }

    function toggleStar(element, id) {
        // Toggle visual
        element.classList.toggle('active');
        const icon = element.querySelector('i');
        const row = element.closest('.email-row');
        let isStarred = false;

        if (element.classList.contains('active')) {
            icon.classList.remove('bi-star');
            icon.classList.add('bi-star-fill');
            isStarred = true;
        } else {
            icon.classList.remove('bi-star-fill');
            icon.classList.add('bi-star');
            isStarred = false;
        }
        
        // Update data attribute for filtering
        if(row) row.setAttribute('data-starred', isStarred ? 'true' : 'false');
        
        // Update counter (simple incremental/decremental)
        const badge = document.getElementById('count-starred');
        if(badge) {
            let current = parseInt(badge.innerText || '0');
            badge.innerText = isStarred ? current + 1 : Math.max(0, current - 1);
        }

        // AJAX Call
        // Ignore virtual tasks for starring (unless we implement virtual storage)
        if(id && !id.toString().startsWith('task_')) {
            fetch(`{{ url('/notifications') }}/${id}/toggle-star`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            }).catch(e => console.error(e));
        }
    }

    function filterEmails(filterType, element) {
        // Active State
        document.querySelectorAll('.sidebar-item').forEach(el => el.classList.remove('active'));
        if(element) element.classList.add('active');

        const rows = document.querySelectorAll('.email-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const category = row.getAttribute('data-category');
            const isRead = row.getAttribute('data-read') === 'true';
            const isStarred = row.getAttribute('data-starred') === 'true';

            let show = false;
            switch(filterType) {
                case 'all':
                    show = true;
                    break;
                case 'unread':
                    show = !isRead;
                    break;
                case 'read':
                    show = isRead;
                    break;
                case 'starred':
                    show = isStarred;
                    break;
                case 'vendor':
                    show = (category === 'vendor');
                    break;
                case 'division':
                    show = (category === 'division');
                    break;
            }

            row.style.display = show ? 'flex' : 'none';
            if(show) visibleCount++;
        });

        // Toggle Empty State
        const emptyState = document.getElementById('filterEmptyState');
        if(visibleCount === 0 && rows.length > 0) {
            emptyState.classList.remove('d-none');
        } else {
            emptyState.classList.add('d-none');
        }
    }

    function searchEmails() {
        const queryRaw = document.getElementById('searchNotif').value.toLowerCase();
        const rows = document.querySelectorAll('.email-row');
        
        // Remove previous highlights
        document.querySelectorAll('.highlight').forEach(mark => {
            const parent = mark.parentNode;
            parent.replaceChild(document.createTextNode(mark.textContent), mark);
            parent.normalize();
        });

        // Use active filter as base if no query
        if(!queryRaw.trim()) {
            const activeFilter = document.querySelector('.sidebar-item.active');
            if(activeFilter) activeFilter.click();
            return;
        }

        // Parse search query for operators like "in:sent" or "label:vendor"
        // Valid mappings: 
        // in:sent -> category=vendor (conceptually, items sent to vendor / waiting vendor)
        // in:inbox -> category=inbox or division
        // in:read -> is_read=true
        // in:unread -> is_read=false
        // in:starred -> is_starred=true
        
        let terms = [];
        let filters = {};

        const tokens = queryRaw.split(/\s+/);
        tokens.forEach(token => {
            if(token.startsWith('in:') || token.startsWith('label:')) {
                const val = token.split(':')[1];
                if(val === 'sent' || val === 'vendor') filters.category = 'vendor';
                else if(val === 'inbox') filters.category = 'inbox'; // or division?
                else if(val === 'division') filters.category = 'division';
                else if(val === 'read') filters.read = true;
                else if(val === 'unread') filters.read = false;
                else if(val === 'starred') filters.starred = true;
            } else {
                if(token.length > 0) terms.push(token);
            }
        });

        rows.forEach(row => {
            const sender = row.querySelector('.email-sender');
            const subject = row.querySelector('.email-subject');
            const snippet = row.querySelector('.email-snippet');
            
            const senderText = sender.textContent.toLowerCase();
            const subjectText = subject.textContent.toLowerCase();
            const snippetText = snippet.textContent.toLowerCase();
            const fullText = senderText + ' ' + subjectText + ' ' + snippetText;

            // 1. Text Match (AND logic)
            const textMatch = terms.length === 0 || terms.every(term => fullText.includes(term));
            
            // 2. Filter Match
            let filterMatch = true;
            if(filters.category) {
                // "sent" maps to "vendor" in our category logic for internal view (items At Vendor)
                if(row.getAttribute('data-category') !== filters.category) filterMatch = false;
            }
            if(filters.read !== undefined) {
                const isRead = row.getAttribute('data-read') === 'true';
                if(isRead !== filters.read) filterMatch = false;
            }
            if(filters.starred !== undefined) {
                const isStarred = row.getAttribute('data-starred') === 'true';
                if(isStarred !== filters.starred) filterMatch = false;
            }

            if(textMatch && filterMatch) {
                row.style.display = 'flex';
                // Highlight matches for all terms simultaneously
                highlightTerms(sender, terms);
                highlightTerms(subject, terms);
                highlightTerms(snippet, terms);
            } else {
                row.style.display = 'none';
            }
        });
    }

    function highlightText(element, query) {
        if (!query) return;
        // Escape special regex chars in query to be safe
        const safeQuery = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        
        // Use a complex regex to avoid highlighting inside existing tags if we run multiple passes
        // But since we cleaned efficiently at start, we can be simpler.
        // However, we are running loop for TERMS. So term2 might highlight inside an element that term1 already highlighted?
        // Actually, we replace innerHTML. If we have <span class="highlight">word1</span> and we try to highlight word2...
        // It should be fine as long as word2 isn't "span" or "class".
        // To be safe against breaking HTML tags, we should only replace text content nodes, but that's complex in plain JS without a library.
        // Quick fix: regex that ignores HTML tags is hard.
        // Better approach for multi-term: specific highlight function that walks text nodes.
        
        // Let's stick to safe regex for now, but apply it carefully.
        // Or better: construct one big regex for all terms and run it ONCE per element.
        // Let's modify searchEmails to pass all terms to highlightText.
    }

    function highlightTerms(element, terms) {
        if (!terms || terms.length === 0) return;
        
        const text = element.textContent; // Get plain text
        if (!text.trim()) return;

        // Sort terms by length desc to match longest first (though regex engine usually passes left-to-right)
        // Creating a regex like (term1|term2|term3)
        const pattern = terms.map(t => t.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')).join('|');
        const regex = new RegExp(`(${pattern})`, 'gi');
        
        const newHTML = text.replace(regex, '<span class="highlight" style="background-color: #fff59d; font-weight: bold;">$1</span>');
        element.innerHTML = newHTML;
    }

    function openNotification(element) {
        const id = element.getAttribute('data-id');
        const link = element.getAttribute('data-link');
        const isRead = element.getAttribute('data-read') === 'true';

        // 1. Mark as read if needed (AJAX)
        // Only if it's a DB notification (usually plain numeric ID or we check is_stored from server, but here we can try anyway)
        // If it starts with 'task_', it's virtual, no need to mark read in DB usually unless we want to track it there
        
        // Optimistic update
        element.classList.remove('unread');
        element.classList.add('read');
        element.setAttribute('data-read', 'true');
        
        // If not virtual task (assumed if ID is numeric), call API
        if (!id.toString().startsWith('task_') && !isRead) {
             fetch(`{{ url('/notifications') }}/${id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            }).catch(e => console.error(e));
        }

        // 2. Navigate
        if (link && link !== '#') {
            window.location.href = link;
        }
    }
</script>
@endpush
@endsection
