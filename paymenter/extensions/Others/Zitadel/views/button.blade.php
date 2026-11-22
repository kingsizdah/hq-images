@if($forceSso)
<style>
    #login input,
    #login button[type="submit"],
    #login label,
    #login a { display: none !important; }
</style>
@endif

<a href="{{ route('zitadel.redirect') }}"
   class="flex items-center justify-center w-full h-10 mt-2 border border-neutral rounded-md text-primary-100 hover:bg-primary-700">
    Sign in with SSO
</a>
