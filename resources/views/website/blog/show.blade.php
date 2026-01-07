@extends('website.layouts.main')

@section('title', ($blog->meta_title ?? $blog->title) . ' - Blog - ' . config('app.name'))
@section('description', $blog->meta_description ?? \Illuminate\Support\Str::limit(strip_tags($blog->content), 160))

@push('styles')
<style>
    .blog-detail-section {
        padding: 100px 0 60px;
        background: var(--bg-light);
    }

    .blog-detail-container {
        max-width: 900px;
        margin: 0 auto;
    }

    .blog-detail-header {
        margin-bottom: 40px;
    }

    .blog-detail-image {
        width: 100%;
        height: 450px;
        object-fit: cover;
        border-radius: 12px;
        margin-bottom: 30px;
        background: var(--bg-gray);
    }

    .blog-detail-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 20px;
        margin-bottom: 25px;
        font-size: 14px;
        color: var(--text-light);
    }

    .blog-detail-meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .blog-detail-category {
        display: inline-block;
        padding: 6px 16px;
        background: var(--primary-light);
        color: var(--primary-dark);
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: var(--transition);
    }

    .blog-detail-category:hover {
        background: var(--primary-color);
        color: white;
    }

    .blog-detail-title {
        font-size: 42px;
        font-weight: 700;
        color: var(--text-dark);
        line-height: 1.2;
        margin-bottom: 20px;
    }

    .blog-detail-content {
        background: white;
        padding: 50px;
        border-radius: 12px;
        box-shadow: var(--shadow-md);
        margin-bottom: 40px;
    }

    .blog-detail-body {
        font-size: 18px;
        line-height: 1.8;
        color: var(--text-dark);
    }

    .blog-detail-body h2 {
        font-size: 32px;
        font-weight: 600;
        margin: 40px 0 20px;
        color: var(--text-dark);
    }

    .blog-detail-body h3 {
        font-size: 26px;
        font-weight: 600;
        margin: 30px 0 15px;
        color: var(--text-dark);
    }

    .blog-detail-body p {
        margin-bottom: 20px;
    }

    .blog-detail-body ul,
    .blog-detail-body ol {
        margin: 20px 0;
        padding-left: 30px;
    }

    .blog-detail-body li {
        margin-bottom: 10px;
    }

    .blog-detail-body img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 30px 0;
    }

    .blog-detail-body a {
        color: var(--primary-color);
        text-decoration: none;
    }

    .blog-detail-body a:hover {
        text-decoration: underline;
    }

    .blog-detail-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 30px;
        padding-top: 30px;
        border-top: 1px solid var(--border-color);
    }

    .blog-detail-tag {
        padding: 6px 14px;
        background: var(--bg-light);
        border: 1px solid var(--border-color);
        border-radius: 6px;
        font-size: 13px;
        color: var(--text-dark);
        text-decoration: none;
        transition: var(--transition);
    }

    .blog-detail-tag:hover {
        background: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
    }

    .related-blogs {
        margin-top: 50px;
    }

    .related-blogs-title {
        font-size: 28px;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 30px;
    }

    .related-blogs-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 25px;
    }

    .related-blog-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
    }

    .related-blog-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
    }

    .related-blog-image {
        width: 100%;
        height: 180px;
        object-fit: cover;
        background: var(--bg-gray);
    }

    .related-blog-content {
        padding: 20px;
    }

    .related-blog-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 10px;
    }

    .related-blog-title a {
        color: inherit;
        text-decoration: none;
        transition: var(--transition);
    }

    .related-blog-title a:hover {
        color: var(--primary-color);
    }

    .related-blog-meta {
        font-size: 12px;
        color: var(--text-light);
    }

    @media (max-width: 768px) {
        .blog-detail-section {
            padding: 80px 0 40px;
        }

        .blog-detail-content {
            padding: 30px 20px;
        }

        .blog-detail-title {
            font-size: 32px;
        }

        .blog-detail-body {
            font-size: 16px;
        }

        .blog-detail-image {
            height: 300px;
        }

        .related-blogs-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<section class="blog-detail-section">
    <div class="container">
        <div class="blog-detail-container">
            <article>
                @if($blog->featured_image)
                    <img src="{{ $blog->featured_image_url }}" alt="{{ $blog->title }}" class="blog-detail-image">
                @endif

                <div class="blog-detail-header">
                    <div class="blog-detail-meta">
                        @if($blog->category)
                            <a href="{{ route('blog.index', ['category' => $blog->category->id]) }}" class="blog-detail-category">
                                {{ $blog->category->name }}
                            </a>
                        @endif
                        <div class="blog-detail-meta-item">
                            <i class="fas fa-user"></i>
                            <span>{{ $blog->author->name ?? 'Admin' }}</span>
                        </div>
                        <div class="blog-detail-meta-item">
                            <i class="far fa-calendar"></i>
                            <span>{{ $blog->published_at->format('F d, Y') }}</span>
                        </div>
                        <div class="blog-detail-meta-item">
                            <i class="far fa-eye"></i>
                            <span>{{ number_format($blog->views_count) }} views</span>
                        </div>
                    </div>
                    <h1 class="blog-detail-title">{{ $blog->title }}</h1>
                </div>

                <div class="blog-detail-content">
                    <div class="blog-detail-body">
                        {!! $blog->content !!}
                    </div>

                    @if($blog->tags->count() > 0)
                        <div class="blog-detail-tags">
                            <strong style="margin-right: 10px;">Tags:</strong>
                            @foreach($blog->tags as $tag)
                                <a href="{{ route('blog.index', ['tag' => $tag->slug]) }}" class="blog-detail-tag">
                                    #{{ $tag->name }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </article>

            @if($relatedBlogs->count() > 0)
                <div class="related-blogs">
                    <h2 class="related-blogs-title">Related Articles</h2>
                    <div class="related-blogs-grid">
                        @foreach($relatedBlogs as $relatedBlog)
                            <div class="related-blog-card">
                                @if($relatedBlog->featured_image)
                                    <img src="{{ $relatedBlog->featured_image_url }}" alt="{{ $relatedBlog->title }}" class="related-blog-image">
                                @else
                                    <div class="related-blog-image" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));"></div>
                                @endif
                                <div class="related-blog-content">
                                    <h3 class="related-blog-title">
                                        <a href="{{ route('blog.show', $relatedBlog->slug) }}">{{ $relatedBlog->title }}</a>
                                    </h3>
                                    <div class="related-blog-meta">
                                        {{ $relatedBlog->published_at->format('M d, Y') }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
