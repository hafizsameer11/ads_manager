@extends('website.layouts.main')

@section('title', 'Blog - ' . config('app.name'))
@section('description', 'Read our latest blog posts, news, and updates')

@push('styles')
<style>
    .blog-section {
        padding: 80px 0;
        background: var(--bg-light);
    }

    .blog-header {
        text-align: center;
        margin-bottom: 50px;
    }

    .blog-header h1 {
        font-size: 42px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 15px;
    }

    .blog-header p {
        font-size: 18px;
        color: var(--text-light);
    }

    .blog-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 40px;
        justify-content: center;
        align-items: center;
    }

    .filter-search {
        flex: 1;
        min-width: 250px;
        max-width: 400px;
        position: relative;
    }

    .filter-search input {
        width: 100%;
        padding: 12px 45px 12px 16px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 14px;
        transition: var(--transition);
    }

    .filter-search input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.1);
    }

    .filter-categories {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .filter-category {
        padding: 8px 20px;
        background: white;
        border: 1px solid var(--border-color);
        border-radius: 20px;
        text-decoration: none;
        color: var(--text-dark);
        font-size: 14px;
        transition: var(--transition);
    }

    .filter-category:hover,
    .filter-category.active {
        background: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
    }

    .blog-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 30px;
        margin-bottom: 50px;
    }

    .blog-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: var(--shadow-md);
        transition: var(--transition);
        display: flex;
        flex-direction: column;
    }

    .blog-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    .blog-card-image {
        width: 100%;
        height: 220px;
        object-fit: cover;
        background: var(--bg-gray);
    }

    .blog-card-content {
        padding: 25px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .blog-card-category {
        display: inline-block;
        padding: 4px 12px;
        background: var(--primary-light);
        color: var(--primary-dark);
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 12px;
        align-self: flex-start;
    }

    .blog-card-title {
        font-size: 20px;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 12px;
        line-height: 1.4;
    }

    .blog-card-title a {
        color: inherit;
        text-decoration: none;
        transition: var(--transition);
    }

    .blog-card-title a:hover {
        color: var(--primary-color);
    }

    .blog-card-excerpt {
        color: var(--text-light);
        font-size: 14px;
        line-height: 1.6;
        margin-bottom: 15px;
        flex: 1;
    }

    .blog-card-meta {
        display: flex;
        align-items: center;
        gap: 15px;
        font-size: 13px;
        color: var(--text-light);
        padding-top: 15px;
        border-top: 1px solid var(--border-color);
    }

    .blog-card-author {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .blog-empty {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 12px;
        box-shadow: var(--shadow-sm);
    }

    .blog-empty h3 {
        font-size: 24px;
        color: var(--text-dark);
        margin-bottom: 10px;
    }

    .blog-empty p {
        color: var(--text-light);
    }

    .pagination-wrapper {
        display: flex;
        justify-content: center;
        margin-top: 40px;
    }

    @media (max-width: 768px) {
        .blog-header h1 {
            font-size: 32px;
        }

        .blog-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .filter-search {
            width: 100%;
            max-width: 100%;
        }
    }
</style>
@endpush

@section('content')
<section class="blog-section">
    <div class="container">
        <div class="blog-header">
            <h1>Our Blog</h1>
            <p>Stay updated with our latest news, tutorials, and insights</p>
        </div>

        <!-- Filters -->
        <form method="GET" action="{{ route('blog.index') }}" class="blog-filters">
            <div class="filter-search">
                <input type="text" name="search" placeholder="Search articles..." value="{{ request('search') }}">
                <button type="submit" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-light); cursor: pointer;">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <div class="filter-categories">
                <a href="{{ route('blog.index') }}" class="filter-category {{ !request('category') ? 'active' : '' }}">
                    All
                </a>
                @foreach($categories as $category)
                    <a href="{{ route('blog.index', ['category' => $category->id]) }}" 
                       class="filter-category {{ request('category') == $category->id ? 'active' : '' }}">
                        {{ $category->name }} ({{ $category->blogs_count ?? 0 }})
                    </a>
                @endforeach
            </div>
        </form>

        <!-- Blog Grid -->
        @if($blogs->count() > 0)
            <div class="blog-grid">
                @foreach($blogs as $blog)
                    <article class="blog-card">
                        @if($blog->featured_image)
                            <img src="{{ $blog->featured_image_url }}" alt="{{ $blog->title }}" class="blog-card-image">
                        @else
                            <div class="blog-card-image" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));"></div>
                        @endif
                        <div class="blog-card-content">
                            @if($blog->category)
                                <span class="blog-card-category">{{ $blog->category->name }}</span>
                            @endif
                            <h2 class="blog-card-title">
                                <a href="{{ route('blog.show', $blog->slug) }}">{{ $blog->title }}</a>
                            </h2>
                            @if($blog->short_description)
                                <p class="blog-card-excerpt">{{ \Illuminate\Support\Str::limit($blog->short_description, 120) }}</p>
                            @endif
                            <div class="blog-card-meta">
                                <div class="blog-card-author">
                                    <i class="fas fa-user"></i>
                                    <span>{{ $blog->author->name ?? 'Admin' }}</span>
                                </div>
                                <div>
                                    <i class="far fa-calendar"></i>
                                    <span>{{ $blog->published_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($blogs->hasPages())
                <div class="pagination-wrapper">
                    {{ $blogs->links() }}
                </div>
            @endif
        @else
            <div class="blog-empty">
                <h3>No blog posts found</h3>
                <p>Check back soon for new articles!</p>
            </div>
        @endif
    </div>
</section>
@endsection
