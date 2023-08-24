<style>
  #__impersonator-link__ {
    position: fixed;
    display: flex;
    align-items: center;
    bottom: 0;
    right: 0;
    padding: 4px 10px;
    color: #555;
    background: #F5F5F6;
    border: 1px solid #ddd;
    text-decoration: none;
    transform: translateX(calc(100% - 35px));
    transition: transform 0.15s ease;
  }
  #__impersonator-link__:hover {
    transform: translateX(0);
  }
  #__impersonator-link__ svg {
    margin-right: 10px;
  }
</style>

<a id="__impersonator-link__" href="{{ $url }}">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="16" height="16"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.25"><path d="M3 8s2-3 5-3 5 3 5 3-2 3-5 3-5-3-5-3z"/><path d="M8 9.2h0c-.7 0-1.2-.5-1.2-1.2v0c0-.7.6-1.2 1.2-1.2h0c.7 0 1.2.6 1.2 1.2v0c0 .7-.5 1.2-1.2 1.2zM.5 3V1.5c0-.6.4-1 1-1H3M15.5 3V1.5c0-.6-.4-1-1-1H13M.5 13v1.5c0 .6.4 1 1 1H3M15.5 13v1.5c0 .6-.4 1-1 1H13"/></g></svg>

  {{ __('Back to my account') }}
</a>
