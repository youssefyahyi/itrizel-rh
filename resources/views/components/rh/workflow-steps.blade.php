@props(['steps', 'current' => 1])
<div class="workflow-steps">
    @foreach($steps as $i => $step)
    @php
        $num = $i + 1;
        $state = $num < $current ? ''done'' : ($num == $current ? ''active'' : '''''');
        if(isset($step[''rejected'']) && $step[''rejected'']) $state = ''rejected'';
    @endphp
    <div class="workflow-step {{ $state }}">
        <div class="workflow-step-dot">
            @if($state === ''done'') ✓
            @elseif($state === ''rejected'') ✕
            @else {{ $num }}
            @endif
        </div>
        <div class="workflow-step-label">{{ $step[''label''] }}</div>
        @if(isset($step[''user'']))<div class="muted" style="font-size:10px;margin-top:2px;">{{ $step[''user''] }}</div>@endif
    </div>
    @endforeach
</div>