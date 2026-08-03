<div class="dropdown">
    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
        {{ config('abaan.supported_locales')[app()->getLocale()]['flag'] }}
        {{ config('abaan.supported_locales')[app()->getLocale()]['label'] }}
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        @foreach (config('abaan.supported_locales') as $code => $info)
            <li>
                <a class="dropdown-item {{ app()->getLocale() === $code ? 'active' : '' }}"
                   href="{{ route('locale.switch', $code) }}">
                    {{ $info['flag'] }} {{ $info['label'] }}
                </a>
            </li>
        @endforeach
    </ul>
</div>