<!--Export từng nhân viên như admin là view báo cáo còn nhân viên là màn dashboard-->
@php
    $total = [];
@endphp
<table class="table table-primary" border="1px">
    <thead>
        <tr>
            <th>Dự án</th>
            @for ($i = 1; $i <= $daysInMonth; $i++)
                <th>{{ $i }}</th>
            @endfor
        </tr>
    </thead>
    <tbody>
        @foreach ($list as $project)
            <tr>
                <td>{{ $project['nameProject'] }}</td>
                @for ($i = 1; $i <= $daysInMonth; $i++)
                    @php
                        $dayWorks = $project['days'];
                        if (empty($total[$i])) {
                            $total[$i] = 0;
                        }
                    @endphp
                    @foreach ($dayWorks as $day)
                        @if ($day['day_work'] == $i)
                            <td>{{ $day['hours'] }}</td>
                            @php
                                $total[$i] += $day['hours'];
                            @endphp
                        @else
                            <td></td>
                        @endif
                    @endforeach
                @endfor
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th>Total</th>
            @for ($i = 1; $i <= $daysInMonth; $i++)
                <td>{{ $total[$i]==0?"": $total[$i]}}</td>
            @endfor
        </tr>
    </tfoot>
</table>
