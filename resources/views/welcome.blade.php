<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Ecommerce</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />


    </head>
    <body class="antialiased">

        <div class="col-lg-12">
        <div class="trk-card">
            <div class="trk-card__header">
                <h4 class="trk-card__title">CRUD Generator</h4>
            </div>
            <div class="trk-card__body">
                <form action="{{ route('admin.crud-generator.generate') }}" method="POST">
                    @csrf
                     <label for="model_name">Model Name</label>
    <input type="text" name="model_name" id="model_name" value="Product" placeholder="e.g. product" required>
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
        
      
       
    </body>
</html>
