@extends('layouts.mainApp')

@section('content')

<div class="col-sm-12 tabs-content">
    <div class="row justify-content-center cont-m">
        <div class="col-md-12">
            <a href="{{ route('auth.masters.create') }}" class="btn btn-light">Создать мастера</a>
            <h2>{{ $title }}</h2>
            <table class="table policy-table">
                <thead>
                <tr>
                    <th scope="col">Имя</th>
                    <th scope="col">Квалификация</th>
                    <th scope="col">Специальности</th>
                    <th scope="col">Телефон</th>
                    <th scope="col">Почта</th>
                    <th scope="col">Рейтинг</th>
                    <th scope="col">Статус</th>
                    <th scope="col">Местоположение</th>
                    <!-- <th scope="col">Зарегистрирован</th> -->
                    <th scope="col">Действие</th>
                </tr>
                </thead>
                <tbody>
                @foreach($masters as $item)
                <?
                    $masterInfo = $item->getMasterInfo();
                ?>
                    <tr>
                        <td><a href="masters/{{ $masterInfo->id }}">{{ $masterInfo->name }}</a></td>
                        <td>{{ $masterInfo->qualification }}</td>
                        <td>{{ $masterInfo->work }}</td>
                        <td>{{ $masterInfo->phone }}</td>
                        <td>{{ $item->email }}</td>
                        <td>{{ $masterInfo->rating }}</td>
                        <td>{{ $masterInfo->status }}</td>
                        <td>{{ $masterInfo->latitude }};{{ $masterInfo->longitude }}</td>
                        <!-- <td>{{ date('H:i d.m.Y', strtotime($item->created_at->timezone('Europe/Moscow'))) }}</td> -->
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
    let isDelete = confirm("Удалить мастера? Данное действие невозможно отменить!");

    if(isDelete)
    {
        let id = _self.data('id');
        $.ajax({
            headers : {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            dataType: "json",
            data    : { id: id },
            url     : 'masters/delete',
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