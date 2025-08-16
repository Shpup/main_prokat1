<div class="flex items-center gap-2">
  @php $r = (int)request('range', 7); @endphp
  @foreach([7,14,30] as $days)
    <a href="{{ request()->fullUrlWithQuery(['range'=>$days]) }}"
       class="inline-block px-2 py-1 text-sm rounded-md border {{ $r===$days ? 'border-blue-500' : 'border-gray-200' }}">
       +{{ $days }} дней
    </a>
  @endforeach
</div>


