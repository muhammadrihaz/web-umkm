@extends('layouts.admin')
@section('title','Zona Delivery')
@section('content')
<div class="container py-4">
    @include('admin._nav')
    <h1>Zona delivery</h1>
    <form method="post" action="{{ route('admin.delivery-zones.store') }}" class="card mb-4"><div class="card-body">
        @csrf
        <div class="row g-2">
            <div class="col-md-4"><input class="form-control" name="name" placeholder="Nama zona" required></div>
            <div class="col-md-3"><input class="form-control" type="number" min="0" name="fee" placeholder="Ongkir" required></div>
            <div class="col-md-3"><input class="form-control" name="coverage_notes" placeholder="Cakupan"></div>
            <div class="col-md-2"><input type="hidden" name="is_active" value="0"><label><input type="checkbox" name="is_active" value="1" checked> Aktif</label><button class="btn btn-brand w-100 mt-2">Tambah</button></div>
        </div>
    </div></form>
    @forelse($zones as $zone)
        <div class="border rounded p-3 mb-2">
            <form method="post" action="{{ route('admin.delivery-zones.update',$zone) }}">
                @csrf @method('put')
                <div class="row g-2 align-items-center">
                    <div class="col-md-3"><input class="form-control" name="name" value="{{ $zone->name }}" required></div>
                    <div class="col-md-2"><input class="form-control" type="number" min="0" name="fee" value="{{ $zone->fee }}" required></div>
                    <div class="col-md-4"><input class="form-control" name="coverage_notes" value="{{ $zone->coverage_notes }}"></div>
                    <div class="col-md-1"><input type="hidden" name="is_active" value="0"><label><input type="checkbox" name="is_active" value="1" @checked($zone->is_active)> Aktif</label></div>
                    <div class="col-md-2"><button class="btn btn-outline-primary btn-sm">Simpan</button></div>
                </div>
            </form>
            @if($zone->is_active)<form class="mt-2" method="post" action="{{ route('admin.delivery-zones.destroy',$zone) }}">@csrf @method('delete')<button class="btn btn-outline-danger btn-sm">Nonaktifkan</button></form>@endif
        </div>
    @empty
        <p class="text-secondary">Belum ada zona.</p>
    @endforelse
</div>
@endsection
