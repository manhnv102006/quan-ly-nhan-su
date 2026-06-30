@props(['action'])

@if($action === 'approved')
    <span class="badge text-bg-success">Phê duyệt</span>
@elseif($action === 'rejected')
    <span class="badge text-bg-danger">Từ chối</span>
@else
    <span class="badge text-bg-secondary">{{ $action }}</span>
@endif
