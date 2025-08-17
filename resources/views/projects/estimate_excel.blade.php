<table border="1" cellpadding="4" cellspacing="0">
    <thead>
    <tr style="background: #f2f2f2; font-weight: bold">
        <th>Раздел / Позиция</th>
        <th>Кол-во</th>
        <th>Цена (₽)</th>
        <th>Сумма (₽)</th>
        <th>Скидка (%)</th>
        <th>После скидки (₽)</th>
    </tr>
    </thead>
    <tbody>

    {{-- Оборудование --}}
    <tr style="background: #ddd; font-weight: bold">
        <td colspan="6">Оборудование</td>
    </tr>

    @php
        $calc           = $calculated;
        $equipTree      = $calc['equipment']['tree']    ?? [];
        $equipDiscount  = $calc['equipment']['discount'] ?? 0.0;
        $materials      = $calc['materials']['details'] ?? [];
        $matDiscount    = $calc['materials']['discount'] ?? 0.0;
        $servicesStaff  = $calc['services']['staff']    ?? [];
        $srvDiscount    = $calc['services']['discount'] ?? 0.0;
        $delivery       = $calc['services']['delivery'] ?? 0.0;

        function renderTree(array $tree, int $level, float $disc) {
            $html = '';
            foreach ($tree as $category => $node) {
                $indent = str_repeat('  ', $level);
                // заголовок категории
                $html .= "<tr><td colspan=\"6\">{$indent}{$category}</td></tr>";

                // вложенные категории
                if (!empty($node['sub'])) {
                    $html .= renderTree($node['sub'], $level+1, $disc);
                }

                // позиции оборудования
                foreach ($node['equipment'] ?? [] as $name => $item) {
                    $qty   = $item['qty']   ?? 0;
                    $price = $item['price'] ?? 0.0;
                    $sum   = $qty * $price;
                    $after = $sum * (1 - $disc/100);
                    $html .= "<tr>"
                           ."<td>{$indent}{$name}</td>"
                           ."<td>{$qty}</td>"
                           ."<td>".number_format($price,2,'.',',')."</td>"
                           ."<td>".number_format($sum,2,'.',',')."</td>"
                           ."<td>{$disc}</td>"
                           ."<td>".number_format($after,2,'.',',')."</td>"
                           ."</tr>";
                }
            }
            return $html;
        }
    @endphp

    {!! renderTree($equipTree, 0, $equipDiscount) !!}

    <tr style="font-weight:bold">
        <td>Итого по оборудованию</td><td></td><td></td>
        <td>{{ number_format($calc['equipment']['total'] ?? 0,2,'.',',') }}</td>
        <td>{{ $equipDiscount }}</td>
        <td>{{ number_format($calc['equipment']['after_disc'] ?? 0,2,'.',',') }}</td>
    </tr>

    {{-- Материалы --}}
    <tr style="background: #ddd; font-weight: bold">
        <td colspan="6">Материалы</td>
    </tr>
    @foreach($materials as $det)
        @php
            $qty   = $det['qty']   ?? 0;
            $price = $det['price'] ?? 0;
            $sum   = $qty * $price;
            $after = $sum * (1 - $matDiscount/100);
        @endphp
        <tr>
            <td>{{ $det['name'] }}</td>
            <td>{{ $qty }}</td>
            <td>{{ number_format($price,2,'.',',') }}</td>
            <td>{{ number_format($sum,2,'.',',') }}</td>
            <td>{{ $matDiscount }}</td>
            <td>{{ number_format($after,2,'.',',') }}</td>
        </tr>
    @endforeach
    <tr style="font-weight:bold">
        <td>Итого по материалам</td><td></td><td></td>
        <td>{{ number_format($calc['materials']['total'] ?? 0,2,'.',',') }}</td>
        <td>{{ $matDiscount }}</td>
        <td>{{ number_format($calc['materials']['after_disc'] ?? 0,2,'.',',') }}</td>
    </tr>

    {{-- Услуги --}}
    <tr style="background: #ddd; font-weight: bold">
        <td colspan="6">Услуги</td>
    </tr>
    @foreach($servicesStaff as $st)
        @php
            $sumS  = $st['sum'] ?? 0;
            $after = $sumS * (1 - $srvDiscount/100);
        @endphp
        <tr>
            <td>{{ $st['name'] }} ({{ $st['rate_type'] }}, {{ $st['minutes'] }} мин)</td>
            <td>1</td>
            <td>{{ number_format($st['rate'] ?? 0,2,'.',',') }}</td>
            <td>{{ number_format($sumS,2,'.',',') }}</td>
            <td>{{ $srvDiscount }}</td>
            <td>{{ number_format($after,2,'.',',') }}</td>
        </tr>
    @endforeach
    <tr>
        <td>Доставка</td><td>1</td>
        <td>{{ number_format($delivery,2,'.',',') }}</td>
        <td>{{ number_format($delivery,2,'.',',') }}</td>
        <td>{{ $srvDiscount }}</td>
        <td>{{ number_format($delivery * (1 - $srvDiscount/100),2,'.',',') }}</td>
    </tr>
    <tr style="font-weight:bold">
        <td>Итого по услугам</td><td></td><td></td>
        <td>{{ number_format($calc['services']['total'] ?? 0,2,'.',',') }}</td>
        <td>{{ $srvDiscount }}</td>
        <td>{{ number_format($calc['services']['after_disc'] ?? 0,2,'.',',') }}</td>
    </tr>

    {{-- Финальная сводка --}}
    <tr style="font-weight:bold; background: #f2f2f2">
        <td>Подытог</td>
        <td colspan="5" align="right">{{ number_format($calc['subtotal'],2,'.',',') }} ₽</td>
    </tr>
    <tr style="font-weight:bold">
        <td>Налог ({{ $calc['tax_method'] }})</td>
        <td colspan="5" align="right">{{ number_format($calc['tax'],2,'.',',') }} ₽</td>
    </tr>
    <tr style="font-weight:bold; background: #dff5e1">
        <td>Итого</td>
        <td colspan="5" align="right">{{ number_format($calc['total'],2,'.',',') }} ₽</td>
    </tr>

    </tbody>
</table>
