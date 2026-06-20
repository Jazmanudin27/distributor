import os

# Entity definitions
entities = {
    'supplier': {
        'model': 'Supplier',
        'table': 'supplier',
        'pk': 'kode_supplier',
        'pk_type': 'string',
        'fields': [
            {'name': 'kode_supplier', 'label': 'Kode Supplier', 'type': 'text'},
            {'name': 'nama_supplier', 'label': 'Nama Supplier', 'type': 'text'},
            {'name': 'alamat', 'label': 'Alamat', 'type': 'textarea'},
            {'name': 'no_hp', 'label': 'No HP', 'type': 'text'},
            {'name': 'email', 'label': 'Email', 'type': 'email'},
            {'name': 'status', 'label': 'Status (1=Aktif, 0=Non)', 'type': 'number'},
        ]
    },
    'kategori': {
        'model': 'Kategori',
        'table': 'kategori',
        'pk': 'id',
        'pk_type': 'int',
        'fields': [
            {'name': 'nama_kategori', 'label': 'Nama Kategori', 'type': 'text'},
        ]
    },
    'merk': {
        'model': 'Merk',
        'table': 'merk',
        'pk': 'id',
        'pk_type': 'int',
        'fields': [
            {'name': 'nama_merk', 'label': 'Nama Merk', 'type': 'text'},
        ]
    },
    'barang': {
        'model': 'Barang',
        'table': 'barang',
        'pk': 'kode_barang',
        'pk_type': 'string',
        'fields': [
            {'name': 'kode_barang', 'label': 'Kode Barang', 'type': 'text'},
            {'name': 'nama_barang', 'label': 'Nama Barang', 'type': 'text'},
            {'name': 'kategori', 'label': 'Kategori', 'type': 'select', 'options_model': 'Kategori', 'options_field': 'nama_kategori'},
            {'name': 'merk', 'label': 'Merk', 'type': 'select', 'options_model': 'Merk', 'options_field': 'nama_merk'},
            {'name': 'kode_supplier', 'label': 'Kode Supplier', 'type': 'select', 'options_model': 'Supplier', 'options_field': 'kode_supplier'},
            {'name': 'keterangan', 'label': 'Keterangan', 'type': 'textarea'},
            {'name': 'stok_min', 'label': 'Stok Minimal', 'type': 'number'},
            {'name': 'status', 'label': 'Status', 'type': 'number'},
        ]
    },
    'barang_satuan': {
        'model': 'BarangSatuan',
        'table': 'barang_satuan',
        'pk': 'id',
        'pk_type': 'int',
        'fields': [
            {'name': 'kode_barang', 'label': 'Kode Barang', 'type': 'select', 'options_model': 'Barang', 'options_field': 'kode_barang'},
            {'name': 'satuan', 'label': 'Satuan', 'type': 'text'},
            {'name': 'isi', 'label': 'Isi / Konversi', 'type': 'number'},
            {'name': 'harga_pokok', 'label': 'Harga Pokok', 'type': 'number'},
            {'name': 'harga_jual', 'label': 'Harga Jual', 'type': 'number'},
        ]
    },
    'pelanggan': {
        'model': 'Pelanggan',
        'table': 'pelanggan',
        'pk': 'kode_pelanggan',
        'pk_type': 'string',
        'fields': [
            {'name': 'kode_pelanggan', 'label': 'Kode Pelanggan', 'type': 'text'},
            {'name': 'nama_pelanggan', 'label': 'Nama Pelanggan', 'type': 'text'},
            {'name': 'alamat_pelanggan', 'label': 'Alamat Pelanggan', 'type': 'textarea'},
            {'name': 'no_hp_pelanggan', 'label': 'No HP', 'type': 'text'},
            {'name': 'limit_pelanggan', 'label': 'Limit', 'type': 'number'},
            {'name': 'metode_bayar', 'label': 'Metode Bayar', 'type': 'text'},
        ]
    },
    'users': {
        'model': 'User',
        'table': 'users',
        'pk': 'id',
        'pk_type': 'int',
        'fields': [
            {'name': 'name', 'label': 'Username', 'type': 'text'},
            {'name': 'email', 'label': 'Email', 'type': 'email'},
            {'name': 'password', 'label': 'Password (Biarkan kosong jika tidak diubah)', 'type': 'password'},
            {'name': 'role', 'label': 'Role', 'type': 'text'},
            {'name': 'nik', 'label': 'NIK', 'type': 'text'},
            {'name': 'status', 'label': 'Status (1=Aktif)', 'type': 'text'},
        ]
    }
}

def generate_model(key, meta):
    # Only generate if it's not User (User model usually exists)
    if key == 'users':
        return
    model_path = f"app/Models/{meta['model']}.php"
    fillables = "', '".join([f['name'] for f in meta['fields']])
    
    pk_str = f"protected $primaryKey = '{meta['pk']}';"
    type_str = f"protected $keyType = 'string';\n    public $incrementing = false;" if meta['pk_type'] == 'string' else ""
    
    content = f"""<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;
use Illuminate\\Database\\Eloquent\\Model;

class {meta['model']} extends Model
{{
    use HasFactory;

    protected $table = '{meta['table']}';
    {pk_str}
    {type_str}

    protected $fillable = ['{fillables}'];
}}
"""
    with open(model_path, 'w') as f:
        f.write(content)

def generate_controller(key, meta):
    c_name = f"{meta['model']}Controller"
    c_path = f"app/Http/Controllers/{c_name}.php"
    
    # Extra data fetching for dropdowns
    extra_use = ""
    extra_index_vars = ""
    extra_create_vars = ""
    compact_str = f"'{key}s'"
    compact_create = ""
    
    for field in meta['fields']:
        if field['type'] == 'select' and 'options_model' in field:
            extra_use += f"use App\\Models\\{field['options_model']};\n"
            var_name = field['options_model'].lower() + "s"
            extra_create_vars += f"        ${var_name} = \\App\\Models\\{field['options_model']}::all();\n"
            if compact_create == "":
                compact_create = f"'{var_name}'"
            else:
                compact_create += f", '{var_name}'"
                
    if compact_create != "":
        compact_create_arr = f"compact({compact_create})"
    else:
        compact_create_arr = "[]"
        
    validation_rules = []
    for f_meta in meta['fields']:
        if f_meta['name'] == 'password' and key == 'users':
            validation_rules.append(f"'{f_meta['name']}' => 'nullable|string'")
        else:
            validation_rules.append(f"'{f_meta['name']}' => 'required'")
    
    validation_str = ",\n            ".join(validation_rules)

    update_logic = ""
    if key == 'users':
        update_logic = """
        $data = $request->validate([...rules...]);
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = bcrypt($data['password']);
        }
        $row->update($data);
        """.replace("[...rules...]", f"[\n            {validation_str}\n        ]")
        
        store_logic = """
        $data = $request->validate([...rules...]);
        $data['password'] = bcrypt($data['password'] ?? 'password');
        \\App\\Models\\User::create($data);
        """.replace("[...rules...]", f"[\n            {validation_str}\n        ]")
    else:
        update_logic = f"""
        $row->update($request->validate([
            {validation_str}
        ]));
        """
        store_logic = f"""
        \\App\\Models\\{meta['model']}::create($request->validate([
            {validation_str}
        ]));
        """
    
    content = f"""<?php

namespace App\\Http\\Controllers;

use Illuminate\\Http\\Request;
use App\\Models\\{meta['model']};
{extra_use}

class {c_name} extends Controller
{{
    public function index()
    {{
        ${key}s = {meta['model']}::all();
        return view('{key}.index', compact('{key}s'));
    }}

    public function create()
    {{
{extra_create_vars}
        return view('{key}.create', {compact_create_arr});
    }}

    public function store(Request $request)
    {{
{store_logic}
        return redirect()->route('{key}.index')->with('success', 'Data berhasil ditambahkan');
    }}

    public function edit($id)
    {{
        $row = {meta['model']}::findOrFail($id);
{extra_create_vars}
        return view('{key}.edit', array_merge(compact('row'), {compact_create_arr}));
    }}

    public function update(Request $request, $id)
    {{
        $row = {meta['model']}::findOrFail($id);
{update_logic}
        return redirect()->route('{key}.index')->with('success', 'Data berhasil diubah');
    }}

    public function destroy($id)
    {{
        {meta['model']}::findOrFail($id)->delete();
        return redirect()->route('{key}.index')->with('success', 'Data berhasil dihapus');
    }}
}}
"""
    with open(c_path, 'w') as f:
        f.write(content)


def generate_views(key, meta):
    os.makedirs(f"resources/views/{key}", exist_ok=True)
    
    # --- INDEX ---
    th_str = "".join([f"<th>{f['label']}</th>\n" for f in meta['fields']])
    td_str = "".join([f"<td>{{{{ $item->{f['name']} }}}}</td>\n" for f in meta['fields']])
    
    index_content = f"""@extends('layouts.app')
@section('title', 'Data {meta['model']}')
@section('content')
<div class="card">
    <div class="card-header">
        <h2>Daftar {meta['model']}</h2>
        <a href="{{{{ route('{key}.create') }}}}" class="btn btn-primary">+ Tambah Data</a>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success">{{{{ session('success') }}}}</div>
    @endif

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    {th_str}
                    <th width="150px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach(${key}s as $item)
                <tr>
                    {td_str}
                    <td>
                        <a href="{{{{ route('{key}.edit', $item->{meta['pk']}) }}}}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{{{ route('{key}.destroy', $item->{meta['pk']}) }}}}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus data ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
                @if(count(${key}s) == 0)
                <tr>
                    <td colspan="10" class="text-center">Belum ada data</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
"""
    with open(f"resources/views/{key}/index.blade.php", 'w') as f:
        f.write(index_content)
        

    # --- CREATE & EDIT ---
    for mode in ['create', 'edit']:
        form_fields = ""
        for f in meta['fields']:
            val_str = f"old('{f['name']}')" if mode == 'create' else f"old('{f['name']}', $row->{f['name']})"
            
            if f['type'] == 'textarea':
                input_html = f"""<textarea name="{f['name']}" id="{f['name']}" class="form-control" rows="3">{{{{ {val_str} }}}}</textarea>"""
            elif f['type'] == 'select':
                var_name = f['options_model'].lower() + "s"
                val_field = f['options_field']
                # If they select Merk for Barang, they are selecting the nama_merk, so value=nama_merk. 
                # Except if it's Supplier for Barang, value=kode_supplier
                opt_val = f"$opt->{val_field}" 
                
                input_html = f"""<select name="{f['name']}" id="{f['name']}" class="form-control">
                    <option value="">-- Pilih {f['label']} --</option>
                    @foreach(${var_name} as $opt)
                        <option value="{{{{ {opt_val} }}}}" {{{{ {val_str} == {opt_val} ? 'selected' : '' }}}}>{{{{ $opt->{val_field} }}}}</option>
                    @endforeach
                </select>"""
            else:
                extra_attr = ""
                if mode == 'edit' and f['name'] == meta['pk'] and meta['pk_type'] == 'string':
                    extra_attr = "readonly" # Cannot change primary key easily
                
                input_html = f"""<input type="{f['type']}" name="{f['name']}" id="{f['name']}" class="form-control" value="{{{{ {val_str} }}}}" {extra_attr}>"""
                
            form_fields += f"""
            <div class="form-group">
                <label for="{f['name']}">{f['label']}</label>
                {input_html}
                @error('{f['name']}')
                    <span class="text-danger">{{{{ $message }}}}</span>
                @enderror
            </div>
"""
        
        action_route = f"route('{key}.store')" if mode == 'create' else f"route('{key}.update', $row->{meta['pk']})"
        method_field = "" if mode == 'create' else "@method('PUT')"
        title = f"Tambah {meta['model']}" if mode == 'create' else f"Edit {meta['model']}"
        
        content = f"""@extends('layouts.app')
@section('title', '{title}')
@section('content')
<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <h2>{title}</h2>
    </div>
    <div class="card-body">
        <form action="{{{{ {action_route} }}}}" method="POST">
            @csrf
            {method_field}
            {form_fields}
            
            <div style="margin-top: 20px; display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{{{ route('{key}.index') }}}}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
"""
        with open(f"resources/views/{key}/{mode}.blade.php", 'w') as f:
            f.write(content)

for k, m in entities.items():
    print(f"Generating CRUD for {k}...")
    generate_model(k, m)
    generate_controller(k, m)
    generate_views(k, m)

print("Done!")
