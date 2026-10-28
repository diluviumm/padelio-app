@php($size = $size ?? 'md')
@if($player && $player->photo_path)
    <img class="avatar {{ $size }}" src="{{ asset('storage/' . $player->photo_path) }}" alt="{{ $player->name }}" loading="lazy">
@else
    <div class="avatar {{ $size }}" aria-hidden="true">{{ $player?->initials() ?? '?' }}</div>
@endif
