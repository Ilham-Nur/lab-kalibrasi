@extends('layouts.app')
@section('title','Convert ke Aset')
@section('content')
  <div class="page-header"><div><h1 class="page-title">Convert ke Aset</h1><p class="page-subtitle">Receipt item yang sudah diterima dan belum menjadi master aset.</p></div></div>
  @include('hr.partials.alerts')
  @include('assets.procurements._tabs')
  <div class="card"><div class="table-responsive"><table class="data-table"><thead><tr><th>Receipt</th><th>Item</th><th>Qty</th><th>Kondisi</th><th class="th-aksi">Aksi</th></tr></thead><tbody>@forelse($receiptItems as $item)<tr><td><a href="{{ route('assets.convert.show',$item->receipt) }}">{{ $item->receipt?->receipt_number }}</a><div class="td-email-sub">{{ $item->receipt?->procurement?->procurement_number }}</div></td><td>{{ $item->item_name }}</td><td>{{ $item->quantity_received }}</td><td>{{ str_replace('_',' ',ucfirst($item->condition ?? 'good')) }}</td><td><form method="POST" action="{{ route('assets.convert.item',$item) }}">@csrf<button class="btn btn-primary btn-sm">Convert</button></form></td></tr>@empty<tr><td colspan="5"><div class="empty-state"><div class="empty-title">Tidak ada item untuk dikonversi</div></div></td></tr>@endforelse</tbody></table></div>@include('hr.partials.pagination',['paginator'=>$receiptItems])</div>
@endsection
