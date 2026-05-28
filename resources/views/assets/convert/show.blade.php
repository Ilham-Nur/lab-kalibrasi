@extends('layouts.app')
@section('title','Detail Convert Aset')
@section('content')
  <div class="page-header"><div><h1 class="page-title">Convert {{ $receipt->receipt_number }}</h1><p class="page-subtitle">Buat aset dari item penerimaan.</p></div><a class="btn btn-outline btn-sm" href="{{ route('assets.convert.index') }}"><i class="bi bi-arrow-left"></i> Kembali</a></div>
  @include('hr.partials.alerts')
  <div class="card"><div class="table-responsive"><table class="data-table"><thead><tr><th>Item</th><th>Qty Received</th><th>Kondisi</th><th>Converted</th><th class="th-aksi">Aksi</th></tr></thead><tbody>@foreach($receipt->items as $item)<tr><td>{{ $item->item_name }}<div class="td-email-sub">{{ $item->notes }}</div></td><td>{{ $item->quantity_received }}</td><td>{{ str_replace('_',' ',ucfirst($item->condition ?? 'good')) }}</td><td>{{ $item->is_converted_to_asset ? 'Ya' : 'Belum' }}</td><td>@if(! $item->is_converted_to_asset && $item->quantity_received > 0)<form method="POST" action="{{ route('assets.convert.item',$item) }}">@csrf<button class="btn btn-primary btn-sm">Convert</button></form>@else - @endif</td></tr>@endforeach</tbody></table></div></div>
@endsection
