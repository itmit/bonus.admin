@extends('layouts.mainApp')

@section('content')

<div class="col-sm-12 tabs-content">
    <div class="row justify-content-center cont-m">
        <div class="col-md-12">
            <a href="{{ route('auth.departments.create') }}" class="btn btn-light">Создать отдел</a>
            <h2>{{ $title }}</h2>
            <table class="table policy-table">
                <thead>
                <tr>
                    <th scope="col">Наименование</th>
                    <th scope="col">Телефон</th>
                    <th scope="col">Эл. почта</th>
                    <th scope="col">Рейтинг</th>
                    <th scope="col">Зарегистрирован</th>
                    <th scope="col">Действие</th>
                </tr>
                </thead>
                <tbody>
                @foreach($departments as $item)
                <?
                $email = $item->getDepartmentEmail()->email;
                ?>
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->phone }}</td>
                        <td>{{ $email }}</td>
                        <td>{{ $item->rating }}</td>
                        <td>{{ date('H:i d.m.Y', strtotime($item->created_at->timezone('Europe/Moscow'))) }}</td>
                        <td><button type="button" class="btn btn-danger delete-master" data-id="{{$item->id}}">Удалить</button></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div> 

<script>

$(document).on('click', '.delete-master', function() {
    var _self = $(this);
    let isDelete = confirm("Удалить отдел? Данное действие невозможно отменить!");

    if(isDelete)
    {
        let id = _self.data('id');
        $.ajax({
            headers : {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            dataType: "json",
            data    : { id: id },
            url     : 'departments/delete',
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