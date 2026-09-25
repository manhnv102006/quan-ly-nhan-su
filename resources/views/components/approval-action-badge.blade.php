@props(['action'])

@if($action === 'approved')
    <span class="badge text-bg-success">Phê duyệt</span>
@elseif($action === 'rejected')
    <span class="badge text-bg-danger">Từ chối</span>
@elseif($action === 'submitted')
    <span class="badge text-bg-info">Gửi đơn</span>
@elseif($action === 'cancelled')
    <span class="badge text-bg-secondary">Hủy đơn</span>
@else
    <span class="badge text-bg-secondary">{{ $action }}</span>
@endif
