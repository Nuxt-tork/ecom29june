@extends('admin.layouts.master')

@section('title', 'CRUD Generator')

@section('container')
<div class="row">
    <div class="col-lg-12">
        <div class="trk-card">
            <div class="trk-card__header">
                <h4 class="trk-card__title">CRUD Generator</h4>
            </div>
            <div class="trk-card__body">
                <form action="{{ route('admin.crud-generator.generate') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="crudJson">CRUD JSON Configuration</label>
                        <textarea name="crud_json" id="crudJson" rows="25" class="form-control" placeholder="Paste your JSON configuration here..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-success mt-3">
                        <i class="fas fa-bolt"></i> Generate CRUD
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
