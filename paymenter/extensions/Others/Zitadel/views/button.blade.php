@if($forceSso && !request()->has('sso_error'))
<script>
    window.location.replace('{{ route('zitadel.redirect') }}');
</script>
@endif

@if(request()->has('sso_error'))
<div class="w-full text-center text-sm text-error py-4">
    Sign-in with SSO failed: {{ session('error') ?: 'unknown error' }}
    <a class="zitadel-sso-button underline" href="{{ route('zitadel.redirect') }}">Try again</a>
</div>
@endif

@if($forceSso)
<style>
    #login input,
    #login button[type="submit"],
    #login label,
    #login a:not(.zitadel-sso-button) { display: none !important; }
</style>
@endif

<a href="{{ route('zitadel.redirect') }}"
   class="zitadel-sso-button flex items-center justify-center w-full h-10 mt-2 border border-neutral rounded-md text-primary-100 hover:bg-primary-700">
    Sign in with SSO
</a>
