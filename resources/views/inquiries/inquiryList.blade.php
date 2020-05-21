@extends('layouts.mainApp')

@section('content')

<div class="col-sm-12 tabs-content">
    <div class="row justify-content-center cont-m">
        <div class="col-md-12">
            <h2>{{ $title }}</h2>
            <table class="table policy-table">
                <thead>
                <tr>
                    <th scope="col">Заказчик</th>
                    <th scope="col">Мастер</th>
                    <th scope="col">Род работ</th>
                    <th scope="col">Срочность</th>
                    <th scope="col">Описание</th>
                    <th scope="col">Адрес</th>
                    <th scope="col">Статус</th>
                    <th scope="col">Создана</th>
                    <th scope="col">Действие</th>
                </tr>
                </thead>
                <tbody>
                @foreach($inquiries as $item)
                <?
                $detail = $item->getInquiryDetail();
                $client = $item->getClient();
                $master = $item->getMaster();
                ?>
                    <tr>
                        <td>{{ $client->name }}</td>
                        <td>
                            @if ($master)
                                {{ $master->name }}
                            @else
                                Мастер еще не назначен
                            @endif
                        </td>
                        <td>{{ $detail->getWork()->work }}</td>
                        <td>{{ $detail->urgency }}</td>
                        <td>{{ $detail->description }}</td>
                        <td>{{ $detail->address }}</td>
                        <td>{{ $detail->status }}</td>
                        <td>{{ date('H:i d.m.Y', strtotime($item->created_at->timezone('Europe/Moscow'))) }}</td>
                        <td><button type="button" class="btn btn-danger delete-inquiry" data-id="{{$item->id}}">Удалить</button></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div> 

<script>

$(document).on('click', '.delete-inquiry', function() {
    var _self = $(this);
    let isDelete = confirm("Удалить запрос? Данное действие невозможно отменить!");

    if(isDelete)
    {
        let id = _self.data('id');
        $.ajax({
            headers : {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            dataType: "json",
            data    : { id: id },
            url     : 'inquiries/delete',
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