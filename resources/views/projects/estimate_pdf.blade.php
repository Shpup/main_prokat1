<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Смета {{ $estimate->name }} для проекта {{ $project->name }}</title>
    <style>
        /* Обычный вес */
        @font-face {
            font-family: 'DejaVu Sans';
            font-style: normal;
            font-weight: 400;
            font-display: swap;
            src: url('https://cdn.jsdelivr.net/npm/@fontsource/dejavu-sans/files/dejavu-sans-cyrillic-400-normal.woff2') format('woff2');
            unicode-range: U+0400-04FF, U+0500-052F;
        }

        /* Жирный вес */
        @font-face {
            font-family: 'DejaVu Sans';
            font-style: normal;
            font-weight: 700;
            font-display: swap;
            src: url('https://cdn.jsdelivr.net/npm/@fontsource/dejavu-sans/files/dejavu-sans-cyrillic-700-normal.woff2') format('woff2');
            unicode-range: U+0400-04FF, U+0500-052F;
        }

        /* Применяем шрифт ко всему документу */
        body, td, th,h1  {
            font-family: 'DejaVu Sans', sans-serif;
        }

        /* Заголовки и th — жирным */
        h1, th {
            font-family: 'DejaVu Sans', sans-serif;
            font-weight: 700;
        }


        /* при желании подключите Noto Serif целиком, без unicode-range */
        @font-face {
            font-family: 'Noto Serif';
            src: url('https://fonts.gstatic.com/s/notoserif/v23/…woff2') format('woff2');
            font-weight: normal;
            font-style: normal;
        }

        body {
            font-size: 12px;
            line-height: 1.4;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;

        }
        th {
            background-color: #f2f2f2;
        }
        h1 {
            font-size: 18px;
            margin-bottom: 10px;
        }
        p {
            margin: 5px 0;
            font-family: 'dejavu sans', sans-serif;
        }
    </style>
</head>
<body>
<h1>Смета: {{ $estimate->name }}</h1>
<p>Проект: {{ $project->name }}</p>
<p>Клиент: {{ $calculated['client'] ?? 'Нет' }}</p>
<p>Фирма: {{ $calculated['company'] ?? 'Нет' }}</p>

<table>
    <thead>
    <tr>
        <th>Раздел / Позиция</th>
        <th>Кол-во</th>
        <th>Цена (₽)</th>
        <th>Сумма (₽)</th>
        <th>Скидка (%)</th>
        <th>После скидки (₽)</th>
    </tr>
    </thead>
    <tbody>
    <!-- Оборудование -->
    <tr><th colspan="6">Оборудование</th></tr>
    @php
        if (! function_exists('hasEquipment')) {
        function hasEquipment(array $node): bool
        {
            if (! empty($node['equipment'])) {
                return true;
            }
            if (! empty($node['sub'])) {
                foreach ($node['sub'] as $sub) {
                    if (hasEquipment($sub)) {
                        return true;
                    }
                }
            }
            return false;
        }
    }

    if (! function_exists('collectEquipment')) {
        function collectEquipment(array $node, array &$result = []): array
        {
            if (! empty($node['equipment'])) {
                $result = array_merge($result, $node['equipment']);
            }
            if (! empty($node['sub'])) {
                foreach ($node['sub'] as $sub) {
                    collectEquipment($sub, $result);
                }
            }
            return $result;
        }
    }

            if (! function_exists('renderPdfTree')) {
        /**
         * @param  array  $tree     — начиная с топ-категорий
         * @param  float  $discount — сквозная скидка
         * @return string
         */
        function renderPdfTree(array $tree, float $discount): string
        {
            $html = '';

            // Для каждой топ-категории
            foreach ($tree as $categoryName => $node) {

                // 1) Пропускаем, если нет оборудования в этой ветке
                if (! hasEquipment($node)) {
                    continue;
                }

                // 2) Рендерим строку заголовка категории
                $html .= '<tr>';
                $html .= '<td colspan="6" style="font-weight:bold; padding-left:0;">'
                       . htmlspecialchars($categoryName)
                       . '</td>';
                $html .= '</tr>';

                // 3) Собираем всё оборудование «плоско»
                $allEquipment = collectEquipment($node);

                // 4) Рендерим каждую позицию с отступом
                foreach ($allEquipment as $eqName => $eq) {
                    $qty   = $eq['qty']   ?? 0;
                    $price = $eq['price'] ?? 0;
                    $sum   = $price * $qty;
                    $after = $sum * (1 - $discount / 100);

                    $html .= '<tr>';
                    $html .= '<td style="padding-left:20px;">' . htmlspecialchars($eqName) . '</td>';
                    $html .= '<td>' . $qty . '</td>';
                    $html .= '<td>' . number_format($price, 2) . '</td>';
                    $html .= '<td>' . number_format($sum, 2) . '</td>';
                    $html .= '<td>' . $discount . '</td>';
                    $html .= '<td>' . number_format($after, 2) . '</td>';
                    $html .= '</tr>';
                }
            }

            return $html;
        }
    }

    @endphp
    @php
        $equipTree     = $calculated['equipment']['tree'] ?? [];
        $equipDiscount = $calculated['equipment']['discount'] ?? 0;
    @endphp

    {!! renderPdfTree($equipTree, $equipDiscount) !!}

    <tr><td>Итого по оборудованию</td><td></td><td></td><td>{{ number_format($calculated['equipment']['total'], 2) }}</td><td>{{ $calculated['equipment']['discount'] }}</td><td>{{ number_format($calculated['equipment']['after_disc'], 2) }}</td></tr>

    <!-- Материалы -->
    <tr><th colspan="6">Материалы</th></tr>
    @foreach($calculated['materials']['details'] as $det)
        <tr>
            <td>{{ htmlspecialchars($det['name']) }}</td>
            <td>{{ $det['qty'] }}</td>
            <td>{{ number_format($det['price'], 2) }}</td>
            <td>{{ number_format($det['price'] * $det['qty'], 2) }}</td>
            <td>{{ $calculated['materials']['discount'] }}</td>
            <td>{{ number_format(($det['price'] * $det['qty']) * (1 - $calculated['materials']['discount']/100), 2) }}</td>
        </tr>
    @endforeach
    <tr><td>Итого по материалам</td><td></td><td></td><td>{{ number_format($calculated['materials']['total'], 2) }}</td><td>{{ $calculated['materials']['discount'] }}</td><td>{{ number_format($calculated['materials']['after_disc'], 2) }}</td></tr>

    <!-- Услуги -->
    <tr><th colspan="6">Услуги</th></tr>
    @foreach($calculated['services']['staff'] as $st)
        <tr>
            <td>{{ htmlspecialchars($st['name']) }} ({{ $st['rate_type'] }}, {{ $st['minutes'] }} мин)</td>
            <td>1</td>
            <td>{{ number_format($st['rate'], 2) }}</td>
            <td>{{ number_format($st['sum'], 2) }}</td>
            <td>{{ $calculated['services']['discount'] }}</td>
            <td>{{ number_format($st['sum'] * (1 - $calculated['services']['discount']/100), 2) }}</td>
        </tr>
    @endforeach
    <tr>
        <td>Доставка</td>
        <td>1</td>
        <td>{{ number_format($calculated['services']['delivery'], 2) }}</td>
        <td>{{ number_format($calculated['services']['delivery'], 2) }}</td>
        <td>{{ $calculated['services']['discount'] }}</td>
        <td>{{ number_format($calculated['services']['delivery'] * (1 - $calculated['services']['discount']/100), 2) }}</td>
    </tr>
    <tr><td>Итого по услугам</td><td></td><td></td><td>{{ number_format($calculated['services']['total'], 2) }}</td><td>{{ $calculated['services']['discount'] }}</td><td>{{ number_format($calculated['services']['after_disc'], 2) }}</td></tr>

    <tr><td>Подытог</td><td colspan="5" align="right">{{ number_format($calculated['subtotal'], 2) }} ₽</td></tr>
    <tr><td>Налог ({{ htmlspecialchars($calculated['tax_method']) }})</td><td colspan="5" align="right">{{ number_format($calculated['tax'], 2) }} ₽</td></tr>
    <tr><td>Итого</td><td colspan="5" align="right">{{ number_format($calculated['total'], 2) }} ₽</td></tr>
    </tbody>
</table>
</body>
</html>
