<div class="absolute -top-1.5 -right-1.5 pointer-events-none">
    @if($count > 0)
        <span class="bg-[#064e3b] text-white text-[9px] font-bold w-[16px] h-[16px] rounded-full flex items-center justify-center shadow-md shadow-black/20 leading-none pb-[1px]">
            {{ $count > 9 ? '9+' : $count }}
        </span>
    @endif
</div>
