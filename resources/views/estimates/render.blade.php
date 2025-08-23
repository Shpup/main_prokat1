<tbody id="estimateTableBody">
@php
    if (! function_exists('renderTree1')) {
        function renderTree1($tree, $level = 0, $isMain = true, $project, $currentEstimate) {
            $html = '';
            if (empty($tree)) {
                \Log::debug('Tree is empty at level ' . $level . ', isMain: ' . ($isMain ? 'true' : 'false'));
                return $html;
            }

            foreach ($tree as $catName => $node) {
                \Log::debug('Processing category: ' . $catName . ' at level ' . $level . ', Node: ' . json_encode($node));
                $hasEquipment = !empty($node['equipment']) && is_array($node['equipment']);
                $hasSub = !empty($node['sub']) && is_array($node['sub']);
                $hasSubEquipment = false;
                if ($hasSub) {
                    foreach ($node['sub'] as $subNode) {
                        if (!empty($subNode['equipment']) || !empty($subNode['sub'])) {
                            $hasSubEquipment = true;
                            break;
                        }
                    }
                }

                if ($hasEquipment || $hasSubEquipment) {
                    $class = $isMain ? 'bg-gray-100 font-bold' : 'font-bold';
                    $padding = $isMain ? '' : 'pl-' . ($level * 4);
                    $colspan = $project->status !== 'completed' ? 9 : 8;
                    $html .= '<tr class="' . $class . '" data-level="' . $level . '"><td class="p-2 border ' . $padding . '" colspan="' . $colspan . '">' . htmlspecialchars($catName) . '</td></tr>';
                    if ($hasSub || $hasEquipment) {
                        $html .= '<tr class="catalog-sub" style="display: none;"><td colspan="' . $colspan . '" class="p-0"><div class="pl-' . ($level + 1) * 4 . '">';
                    }

                    if ($hasEquipment) {
                        foreach ($node['equipment'] as $eqKey => $eq) {
                            \Log::debug('Rendering equipment: ' . $eq['name'] . ', Key: ' . $eqKey . ', Data: ' . json_encode($eq));
                            $sum = ($eq['price'] ?? 0) * ($eq['coefficient'] ?? 1.0) * ($eq['qty'] ?? 1);
                            $afterDiscount = $sum * (1 - (($eq['discount'] ?? 0) / 100));
                            $html .= '<tr data-equipment-id="' . ($eq['id'] ?? 0) . '" data-is-consumable="' . (isset($eq['is_consumable']) && $eq['is_consumable'] ? 'true' : 'false') . '">';
                            $html .= '<td class="p-2 border pl-' . (($level + 1) * 4) . '">' . htmlspecialchars($eq['name']) . '</td>';
                            $html .= '<td class="p-2 border pl-' . (($level + 1) * 4) . '">' . htmlspecialchars($eq['description'] ?? '') . '</td>';
                            $html .= '<td class="p-2 border editable" data-field="quantity" data-equipment-id="' . ($eq['id'] ?? 0) . '">' . ($eq['qty'] ?? 1) . '</td>';
                            $html .= '<td class="p-2 border editable" data-field="price" data-equipment-id="' . ($eq['id'] ?? 0) . '">' . number_format($eq['price'] ?? 0, 2) . '</td>';
                            $html .= '<td class="p-2 border editable" data-field="coefficient" data-equipment-id="' . ($eq['id'] ?? 0) . '">' . number_format($eq['coefficient'] ?? 1.0, 2) . '</td>';
                            $html .= '<td class="p-2 border">' . number_format($sum, 2) . '</td>';
                            $html .= '<td class="p-2 border editable" data-field="discount" data-equipment-id="' . ($eq['id'] ?? 0) . '">' . number_format($eq['discount'] ?? 0, 2) . '</td>';
                            $html .= '<td class="p-2 border">' . number_format($afterDiscount, 2) . '</td>';
                            if ($project->status !== 'completed') {
                                $html .= '<td class="p-2 border"><button onclick="removeEquipment(' . ($eq['id'] ?? 0) . ', ' . ($currentEstimate->id ?? 0) . ')" class="text-red-600 hover:text-red-800"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button></td>';
                            }
                            $html .= '</tr>';
                        }
                    }

                    if ($hasSub && $hasSubEquipment) {
                        $html .= renderTree1($node['sub'], $level + 1, false, $project, $currentEstimate);
                    }

                    if ($hasSub || $hasEquipment) {
                        $html .= '</div></td></tr>';
                    }
                } else {
                    \Log::debug('Skipping category: ' . $catName . ' - no equipment or non-empty subcategories');
                }
            }
            return $html;
        }
    }
@endphp
@foreach(['equipment' => 'Оборудование', 'materials' => 'Материалы', 'services' => 'Услуги'] as $key => $label)
    @php
        $tree = $currentEstimate->calculated[$key]['tree'] ?? [];
        $total = $currentEstimate->calculated[$key]['total'] ?? 0;
        $discount = $currentEstimate->calculated[$key]['discount'] ?? 0;
        $afterDisc = $currentEstimate->calculated[$key]['after_disc'] ?? 0;
        \Log::debug("Rendering section: $key, Tree: " . json_encode($tree));
        $hasContent = false;
        if (!empty($tree)) {
            foreach ($tree as $node) {
                if (!empty($node['equipment']) || !empty($node['sub'])) {
                    $hasContent = true;
                    break;
                }
            }
        }
    @endphp
    @if ($hasContent || ($key === 'services' && (!empty($currentEstimate->calculated['services']['staff']) || $currentEstimate->calculated['services']['delivery'] > 0)))
        <tr class="bg-gray-100 font-bold">
            <td class="p-2 border" colspan="{{ $project->status !== 'completed' ? 9 : 8 }}">{{ $label }}</td>
        </tr>
        {!! renderTree1($tree, 0, true, $project, $currentEstimate) !!}
        @if ($key === 'services')
            @foreach($currentEstimate->calculated['services']['staff'] as $st)
                <tr data-staff-id="{{ $st['id'] ?? 0 }}">
                    <td class="p-2 border pl-4">{{ $st['name'] }}</td>
                    <td class="p-2 border pl-4">{{ $st['description'] ?? '' }}</td>
                    <td class="p-2 border">{{ number_format($st['minutes'] / 60, 2) }}</td>
                    <td class="p-2 border editable" data-field="rate" data-staff-id="{{ $st['id'] ?? 0 }}">{{ number_format($st['rate'] ?? 0, 2) }}</td>
                    <td class="p-2 border editable" data-field="coefficient" data-staff-id="{{ $st['id'] ?? 0 }}">{{ number_format($st['coefficient'] ?? 1.0, 2) }}</td>
                    <td class="p-2 border">{{ number_format($st['sum'], 2) }}</td>
                    <td class="p-2 border editable" data-field="discount" data-staff-id="{{ $st['id'] ?? 0 }}">{{ number_format($st['discount'] ?? 0, 2) }}</td>
                    <td class="p-2 border">{{ number_format($st['sum'] * (1 - ($currentEstimate->calculated['services']['discount'] / 100)), 2) }}</td>
                    @if($project->status !== 'completed')
                        <td class="p-2 border"></td>
                    @endif
                </tr>
            @endforeach
            @if($currentEstimate->calculated['services']['delivery'] > 0)
                <tr>
                    <td class="p-2 border pl-4">Доставка</td>
                    <td class="p-2 border pl-4"></td>
                    <td class="p-2 border"></td>
                    <td class="p-2 border"></td>
                    <td class="p-2 border"></td>
                    <td class="p-2 border">{{ number_format($currentEstimate->calculated['services']['delivery'], 2) }}</td>
                    <td class="p-2 border">{{ number_format($currentEstimate->calculated['services']['discount'], 2) }}</td>
                    <td class="p-2 border">{{ number_format($currentEstimate->calculated['services']['delivery'] * (1 - ($currentEstimate->calculated['services']['discount'] / 100)), 2) }}</td>
                    @if($project->status !== 'completed')
                        <td class="p-2 border"></td>
                    @endif
                </tr>
            @endif
            @if ($total > 0 || !empty($currentEstimate->calculated['services']['staff']) || $currentEstimate->calculated['services']['delivery'] > 0)
                <tr class="font-bold">
                    <td class="p-2 border">Итого {{ strtolower($label) }}</td>
                    <td class="p-2 border"></td>
                    <td class="p-2 border"></td>
                    <td class="p-2 border"></td>
                    <td class="p-2 border"></td>
                    <td class="p-2 border" id="total-{{ $key }}">{{ number_format($total, 2) }}</td>
                    <td class="p-2 border" id="discount-{{ $key }}">{{ number_format($discount, 2) }}</td>
                    <td class="p-2 border" id="after-disc-{{ $key }}">{{ number_format($afterDisc, 2) }}</td>
                    @if($project->status !== 'completed')
                        <td class="p-2 border"></td>
                    @endif
                </tr>
            @endif
        @endif
        @if ($key !== 'services' && $total > 0)
            <tr class="font-bold">
                <td class="p-2 border">Итого {{ strtolower($label) }}</td>
                <td class="p-2 border"></td>
                <td class="p-2 border"></td>
                <td class="p-2 border"></td>
                <td class="p-2 border"></td>
                <td class="p-2 border" id="total-{{ $key }}">{{ number_format($total, 2) }}</td>
                <td class="p-2 border" id="discount-{{ $key }}">{{ number_format($discount, 2) }}</td>
                <td class="p-2 border" id="after-disc-{{ $key }}">{{ number_format($afterDisc, 2) }}</td>
                @if($project->status !== 'completed')
                    <td class="p-2 border"></td>
                @endif
            </tr>
        @endif
    @endif
@endforeach
<tr class="font-bold">
    <td class="p-2 border">Подытог</td>
    <td class="p-2 border" colspan="{{ $project->status !== 'completed' ? 7 : 6 }}" id="subtotal">{{ number_format($currentEstimate->calculated['subtotal'] ?? 0, 2) }} ₽</td>
    @if($project->status !== 'completed')
        <td class="p-2 border"></td>
    @endif
</tr>
<tr class="font-bold">
    <td class="p-2 border">Налог ({{ $currentEstimate->calculated['tax_method'] ?? 'none' }})</td>
    <td class="p-2 border" colspan="{{ $project->status !== 'completed' ? 7 : 6 }}" id="tax">{{ number_format($currentEstimate->calculated['tax'] ?? 0, 2) }} ₽</td>
    @if($project->status !== 'completed')
        <td class="p-2 border"></td>
    @endif
</tr>
<tr class="font-bold">
    <td class="p-2 border">Итого</td>
    <td class="p-2 border" colspan="{{ $project->status !== 'completed' ? 7 : 6 }}" id="total">{{ number_format($currentEstimate->calculated['total'] ?? 0, 2) }} ₽</td>
    @if($project->status !== 'completed')
        <td class="p-2 border"></td>
    @endif
</tr>
</tbody>
