@if(session('status') || $errors->any())
    <div class="toast-wrap">
        @if(session('status'))
            <div class="toast ok" role="status">
                <svg style="width:1.25em;height:1.25em;flex:none"><use href="#i-check"/></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif
        @foreach($errors->all() as $error)
            <div class="toast err" role="alert">
                <svg style="width:1.25em;height:1.25em;flex:none"><use href="#i-x"/></svg>
                <span>{{ $error }}</span>
            </div>
        @endforeach
    </div>
@endif
