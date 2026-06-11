@php
    use App\Enums\ArticleVisibility;

    $title = $article->meta_title ?: $article->title;
    $description = $article->meta_description ?: $article->excerpt;
@endphp

<x-layouts::wrapper
    :title="$title"
    class="min-h-screen bg-white text-zinc-950 antialiased dark:bg-zinc-950 dark:text-white"
>
    @push('meta')
        @if ($description)
            <meta name="description" content="{{ $description }}">
        @endif

        <link rel="canonical" href="{{ $article->url() }}">
    @endpush

    <article class="mx-auto flex w-full max-w-3xl flex-col gap-8 px-6 py-12 lg:py-16">
        <header class="flex flex-col gap-4">
            <div class="flex flex-wrap items-center gap-3 text-sm text-zinc-500 dark:text-zinc-400">
                @if ($article->published_at)
                    <time datetime="{{ $article->published_at->toISOString() }}">
                        {{ $article->published_at->toFormattedDateString() }}
                    </time>
                @endif

                @if ($article->visibility === ArticleVisibility::Authenticated)
                    <span>Authenticated</span>
                @endif
            </div>

            <h1 class="text-4xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                {{ $article->title }}
            </h1>

            @if ($article->excerpt)
                <p class="text-lg leading-8 text-zinc-600 dark:text-zinc-300">
                    {{ $article->excerpt }}
                </p>
            @endif
        </header>

        <div class="prose prose-zinc max-w-none dark:prose-invert">
            {!! $article->renderRichContent('body') !!}
        </div>
    </article>
</x-layouts::wrapper>