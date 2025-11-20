@extends('layouts.app')

@section('title', 'List Project - PT PAL Indonesia')

@push('styles')
<style>
    /* --- Wrapper Card Besar --- */
    .big-card {
        background: #e8e8e8 !important;
        border-radius: 18px !important;
        padding: 50px !important;
        min-height: 550px;
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.12);
    }

    /* --- Search Bar --- */
    .search-box {
        width: 45%;
        margin: 0 auto 40px auto;
        position: relative;
    }

    .search-input {
        width: 100%;
        height: 38px;
        padding: 0 45px 0 20px;
        border-radius: 30px;
        background: white;
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    .search-icon {
        position: absolute;
        right: 18px;
        top: 50%;
        transform: translateY(-50%);
        opacity: .6;
        cursor: pointer;
    }

    /* --- Grid Project Cards --- */
    .project-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 35px;
        justify-items: center;
    }

    @media (max-width: 1200px) {
        .project-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .project-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 480px) {
        .project-grid {
            grid-template-columns: 1fr;
        }
    }

    /* --- Card Project --- */
    .project-card {
        width: 260px;
        background: #ffffff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transition: .2s ease;
    }

    .project-card:hover {
        transform: translateY(-5px);
    }

    .project-image {
        width: 100%;
        height: 160px;
        object-fit: cover;
        background: #ddd;
    }

    .project-body {
        padding: 18px;
    }

    .label {
        font-size: 12px;
        color: #555;
    }

    .project-name {
        font-weight: 600;
        font-size: 17px;
        margin: 2px 0 25px 0;
    }

    .project-link {
        color: #0066cc;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
    }

    /* -------- ACTIVE NAVBAR STYLE -------- */
    .nav-active {
        color: black !important;
        font-weight: 700;
        border-bottom: 2px solid black;
        padding-bottom: 4px;
    }
</style>
@endpush


@section('content')

<div class="row">
    <div class="col-12">

        <!-- TITLE -->
        <h2 class="fw-bold mb-4">LIST PROJECT</h2>

        <!-- CARD BESAR -->
        <div class="card big-card">

            <!-- SEARCH BAR -->
            <form class="d-flex gap-2" id="searchForm" style="flex: 0 0 auto;">
                <div class="search-box">
                    <div class="position-relative" style="width: 750px;">
                        <input type="text"
                            class="form-control pe-5"
                            name="search"
                            id="searchInput"
                            placeholder="Cari Vendor..."
                            autocomplete="off">
                        <button type="button"
                            class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-danger pe-2"
                            id="clearSearch"
                            style="z-index: 10; display: none;">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </div>
                </div>
            </form>


            <!-- GRID PROJECT -->
            <div class="project-grid">

                @foreach($projects as $project)
                <div class="project-card"
                    data-name="{{ strtolower($project->project_name ?? '') }}">

                    <img
                        src="{{ asset('images/' . ($project->image ?? 'assetimgkapal1.jpg')) }}"
                        class="project-image"
                        alt="{{ $project->project_name ?? 'Project' }}">

                    <div class="project-body">
                        <p class="label">Nama Project</p>

                        <h3 class="project-name">
                            {{ $project->project_name ?? 'Nama Tidak Ditemukan' }}
                        </h3>

                        <a class="project-link" href="{{ route('desain.daftar-pengadaan', $project->project_id) }}">
                            GO TO PROJECT →
                        </a>
                    </div>
                </div>

                @endforeach

            </div>

        </div>
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
    const searchInput = document.getElementById('searchInput'); // ✅ ID yang benar
    const clearBtn = document.getElementById('clearSearch');
    const cards = document.querySelectorAll('.project-card');

    // Search dengan debouncing
    searchInput.addEventListener('input', function() {
        const value = this.value.trim().toLowerCase();

        // Show/hide clear buttonyyyyx`
        clearBtn.style.display = value ? 'block' : 'none';

        // Debounce search
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            performSearch(value);
        }, 300); // Delay 300ms
    });

    // Clear search button
    clearBtn.addEventListener('click', function() {
        searchInput.value = '';
        this.style.display = 'none';
        performSearch(''); // Reset tampilkan semua
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

        // Optional: Tampilkan pesan jika tidak ada hasil
        if (visibleCount === 0 && searchValue !== '') {
            // Bisa tambahkan alert atau message
            console.log('Tidak ada project yang ditemukan');
        }
    }
</script>
@endpush
