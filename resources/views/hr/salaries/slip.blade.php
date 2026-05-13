@extends('layouts.app')
@section('title', 'Slip Gaji')
@section('content')
  <div class="card"><div class="modal-body"><h1 class="page-title">Slip Gaji</h1><p>{{ $salary->salary_period }}</p><hr><p><strong>{{ $salary->employee?->nama }}</strong><br>{{ $salary->employee?->division?->name }} - {{ $salary->employee?->position?->name }}</p><table class="data-table"><tr><td>Gaji Pokok</td><td>Rp {{ number_format($salary->basic_salary,0,',','.') }}</td></tr><tr><td>Tunjangan</td><td>Rp {{ number_format($salary->allowance,0,',','.') }}</td></tr><tr><td>Lembur</td><td>Rp {{ number_format($salary->overtime,0,',','.') }}</td></tr><tr><td>Potongan</td><td>Rp {{ number_format($salary->deduction,0,',','.') }}</td></tr><tr><th>Total</th><th>Rp {{ number_format($salary->total_salary,0,',','.') }}</th></tr></table></div></div>
@endsection
