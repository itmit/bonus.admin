@extends('layouts.mainApp')

@section('content')

<div class="col-sm-12 tabs-content">
    <div class="row justify-content-center cont-m">
        <div class="col-md-12">
            <h2>{{ $title }}</h2>
            <table class="table policy-table">
                <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Имя</th>
                    <th scope="col">Дата создания</th>
                    <th scope="col">Действие</th>
                </tr>
                </thead>
                <tbody>
                @foreach($news as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td><a href="news/{{ $item->id }}">{{ $item->name }}</a></td>
                        <td>{{ $item->created_at }}</td>
                        <td><button type="button" class="btn btn-danger delete-item" data-id="{{$item->id}}">Удалить</button></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div> 

<script>

$(document).on('click', '.delete-item', function() {
    var _self = $(this);
    let isDelete = confirm("Удалить? Данное действие невозможно отменить!");

    if(isDelete)
    {
        let id = _self.data('id');
        $.ajax({
            headers : {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            dataType: "json",
            data    : { id: id },
            url     : 'news/delete',
            method    : 'delete',
            success: function (response) {
                _self.closest('tr').remove();
                console.log('Удалено!');
            },
            error: function (xhr, err) { 
                console.log("Error: " + xhr + " " + err);
            }
        });
    }
});

</script>
@endsection