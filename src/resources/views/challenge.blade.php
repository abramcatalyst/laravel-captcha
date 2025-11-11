<div class="captcha-container" style="margin: 10px 0;">
    {{-- IMAGE CAPTCHA --}}
    @if (($challenge['type'] ?? null) === 'image' && !empty($challenge['image_url']))
        <div>
            <img src="{{ $challenge['image_url'] }}" alt="CAPTCHA" style="border: 1px solid #ddd; margin-bottom: 5px;">

            @if (!$hasGD)
                <div style="color: #e74c3c; font-size: 12px; margin-top: 5px;">
                    ⚠️ Image CAPTCHA requires GD extension. Install with:
                    <code>apt-get install php-gd</code>
                </div>
            @endif
        </div>

        {{-- TEXT-BASED CAPTCHA: math, word --}}
    @elseif (in_array($challenge['type'] ?? '', ['math', 'word']) && !empty($challenge['question']))
        <div style="margin-bottom: 5px;">
            <label for="captcha" style="display: block; font-weight: bold; margin-bottom: 2px;">
                {!! $challenge['question'] !!}
            </label>
        </div>
    @endif

    {{-- INPUT FIELD --}}
    <div style="margin-bottom: 5px;">
        <input type="text" name="captcha" id="captcha" required autocomplete="off" placeholder="Enter CAPTCHA"
            style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 150px;">
    </div>

    {{-- Hidden field to track CAPTCHA type --}}
    <input type="hidden" name="captcha_type" value="{{ $challenge['type'] ?? 'unknown' }}">

    {{-- WARNING MESSAGE (e.g. fallback notice) --}}
    @if (!empty($challenge['warning']))
        <div style="color: #f39c12; font-size: 12px; margin-top: 5px;">
            ⚠️ {{ $challenge['warning'] }}
        </div>
    @endif

    {{-- ERROR MESSAGE --}}
    @error('captcha')
        <div style="color: #e74c3c; font-size: 12px; margin-top: 5px;">
            {{ $message }}
        </div>
    @enderror
</div>
