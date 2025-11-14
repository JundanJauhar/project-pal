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
        box-shadow: 0 6px 12px rgba(0,0,0,0.12);
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
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
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

    /* --- Card Project --- */
    .project-card {
        width: 260px;
        background: #ffffff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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
            <div class="search-box">
                <input type="text" class="search-input" id="search-project">
                <span class="search-icon">🔍</span>
            </div>

            <!-- GRID PROJECT -->
            <div class="project-grid">

                @foreach($projects as $project)
                <div class="project-card"
                    data-name="{{ strtolower($project->name ?? $project->project_name ?? '') }}">

                    <img 
                        src="{{ asset('images/' . ($project->image ?? 'assetimgkapal1.jpg')) }}"
                        class="project-image">

                    <div class="project-body">
                        <p class="label">Nama Project</p>

                        <h3 class="project-name">
                            {{ $project->name 
                                ?? $project->project_name 
                                ?? $project->name_project 
                                ?? 'Nama Tidak Ditemukan' }}
                        </h3>

                        <a class="project-link" href="{{ route('desain.daftar-permintaan', $project->project_id) }}">
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
</script>
@endpush

@push('scripts')
<script>
    // Set navbar menu "Projects" menjadi aktif
    const projectNav = document.getElementById('nav-projects');
    if (projectNav) {
        projectNav.classList.add('nav-active');
    }

    // ----- Searchable Project Cards -----
    const searchInput = document.getElementById('search-project');
    const cards = document.querySelectorAll('.project-card');

    searchInput.addEventListener('keyup', function () {
        const value = this.value.toLowerCase();

        cards.forEach(card => {
            const name = card.getAttribute('data-name');

            if (name.includes(value)) {
                card.style.display = "block";
            } else {
                card.style.display = "none";
            }
        });
    });
</script>
@endpush

