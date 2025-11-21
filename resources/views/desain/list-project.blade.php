@extends('layouts.app')

@section('title', 'List Project - PT PAL Indonesia')

@push('styles')
<style>
    /* --- Page Container --- */
    .page-container {
        background: #e8e8e8;
        min-height: calc(100vh - 100px);
        padding: 40px 60px;
    }

    /* --- Header Section --- */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 40px;
    }

    .page-title {
        font-size: 28px;
        font-weight: 700;
        color: #000;
        margin: 0;
    }

    /* --- Search Bar --- */
    .search-wrapper {
        position: relative;
        width: 350px;
    }

    .search-input {
        width: 100%;
        height: 42px;
        padding: 0 45px 0 20px;
        border-radius: 25px;
        background: white;
        border: 1px solid #ddd;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        font-size: 14px;
    }

    .search-input:focus {
        outline: none;
        border-color: #0066cc;
        box-shadow: 0 2px 8px rgba(0, 102, 204, 0.2);
    }

    .search-icon {
        position: absolute;
        right: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #666;
        cursor: pointer;
    }

    .clear-search {
        position: absolute;
        right: 45px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #dc3545;
        cursor: pointer;
        padding: 0;
        font-size: 18px;
        display: none;
    }

    /* --- Grid Project Cards --- */
    .project-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 30px;
        padding: 20px 0;
    }

    @media (max-width: 1400px) {
        .project-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 1024px) {
        .project-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 640px) {
        .project-grid {
            grid-template-columns: 1fr;
        }
        
        .search-wrapper {
            width: 100%;
        }
        
        .page-header {
            flex-direction: column;
            gap: 20px;
            align-items: flex-start;
        }
    }

    /* --- Card Project --- */
    .project-card {
        background: #ffffff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .project-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .project-image {
        width: 100%;
        height: 180px;
        object-fit: cover;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .project-body {
        padding: 20px;
    }

    .project-label {
        font-size: 11px;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
        font-weight: 500;
    }

    .project-name {
        font-weight: 700;
        font-size: 18px;
        color: #000;
        margin: 0 0 20px 0;
        min-height: 50px;
    }

    .project-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #0066cc;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        transition: gap 0.2s ease;
    }

    .project-link:hover {
        gap: 10px;
        color: #0052a3;
    }

    .project-link i {
        font-size: 14px;
    }

    /* --- Empty State --- */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
        display: none;
    }

    .empty-state.show {
        display: block;
    }

    .empty-state i {
        font-size: 48px;
        margin-bottom: 15px;
        opacity: 0.5;
    }

    /* --- Active Navbar Style --- */
    .nav-active {
        color: black !important;
        font-weight: 700;
        border-bottom: 3px solid black;
        padding-bottom: 4px;
    }
</style>
@endpush


@section('content')

<div class="page-container">
    
    <!-- HEADER WITH TITLE AND SEARCH -->
    <div class="page-header">
        <h1 class="page-title">LIST PROJECT</h1>
        
        <!-- SEARCH BAR -->
        <div class="search-wrapper">
            <input type="text"
                class="search-input"
                id="searchInput"
                placeholder="Cari Project..."
                autocomplete="off">
            
            <button type="button"
                class="clear-search"
                id="clearSearch">
                <i class="bi bi-x-circle-fill"></i>
            </button>
            
            <span class="search-icon">
                <i class="bi bi-search"></i>
            </span>
        </div>
    </div>

    <!-- GRID PROJECT -->
    <div class="project-grid" id="projectGrid">
        @foreach($projects as $project)
        <div class="project-card"
            data-name="{{ strtolower($project->project_name ?? '') }}"
            onclick="window.location.href='{{ route('desain.daftar-pengadaan', $project->project_id) }}'">

            <img
                src="{{ asset('images/' . ($project->image ?? 'assetimgkapal1.jpg')) }}"
                class="project-image"
                alt="{{ $project->project_name ?? 'Project' }}">

            <div class="project-body">
                <p class="project-label">Nama Project</p>

                <h3 class="project-name">
                    {{ $project->project_code ?? 'Nama Tidak Ditemukan' }}
                </h3>

                <a class="project-link" 
                   href="{{ route('desain.daftar-pengadaan', $project->project_id) }}"
                   onclick="event.stopPropagation()">
                    GO TO PROJECT <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
        @endforeach
    </div>

    <!-- EMPTY STATE -->
    <div class="empty-state" id="emptyState">
        <i class="bi bi-inbox"></i>
        <p>Tidak ada project yang ditemukan</p>
    </div>

</div>

@endsection


@push('scripts')
<script>
    // Set navbar menu "Projects" menjadi aktif
    const projectNav = document.getElementById('nav-projects');
    if (projectNav) {
        projectNav.classList.add('nav-active');
    }

    // ----- Searchable Project Cards -----
    let searchTimeout;
    const searchInput = document.getElementById('searchInput');
    const clearBtn = document.getElementById('clearSearch');
    const cards = document.querySelectorAll('.project-card');
    const emptyState = document.getElementById('emptyState');
    const projectGrid = document.getElementById('projectGrid');

    // Search dengan debouncing
    searchInput.addEventListener('input', function() {
        const value = this.value.trim().toLowerCase();

        // Show/hide clear button
        clearBtn.style.display = value ? 'block' : 'none';

        // Debounce search
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            performSearch(value);
        }, 300);
    });

    // Clear search button
    clearBtn.addEventListener('click', function() {
        searchInput.value = '';
        this.style.display = 'none';
        performSearch('');
        searchInput.focus();
    });

    // Function untuk filter cards
    function performSearch(searchValue) {
        let visibleCount = 0;

        cards.forEach(card => {
            const name = card.getAttribute('data-name');

            if (name.includes(searchValue)) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // Tampilkan empty state jika tidak ada hasil
        if (visibleCount === 0 && searchValue !== '') {
            projectGrid.style.display = 'none';
            emptyState.classList.add('show');
        } else {
            projectGrid.style.display = 'grid';
            emptyState.classList.remove('show');
        }
    }

    // Enter key untuk search
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            performSearch(this.value.trim().toLowerCase());
        }
    });
</script>
@endpush