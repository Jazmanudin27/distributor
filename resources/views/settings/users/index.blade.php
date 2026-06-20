@extends('layouts.app')
@section('title', 'Data User')
@section('content')
<div class="card">
    <div class="card-header">
        <h2>Daftar User</h2>
        <a href="{{ route('users.create') }}" class="btn btn-primary">+ Tambah Data</a>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Username</th>
<th>Email</th>
<th>Password (Biarkan kosong jika tidak diubah)</th>
<th>Role</th>
<th>NIK</th>
<th>Status (1=Aktif)</th>

                    <th width="150px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($userss as $item)
                <tr>
                    <td>{{ $item->name }}</td>
<td>{{ $item->email }}</td>
<td>{{ $item->password }}</td>
<td>{{ $item->role }}</td>
<td>{{ $item->nik }}</td>
<td>{{ $item->status }}</td>

                    <td>
                        <a href="{{ route('users.edit', $item->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('users.destroy', $item->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus data ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
                @if(count($userss) == 0)
                <tr>
                    <td colspan="10" class="text-center">Belum ada data</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
