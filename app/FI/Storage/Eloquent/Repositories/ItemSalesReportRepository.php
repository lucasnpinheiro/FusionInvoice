<?php namespace FI\Storage\Eloquent\Repositories;

use FI\Classes\CurrencyFormatter;
use FI\Classes\NumberFormatter;
use FI\Storage\Eloquent\Models\InvoiceItem;

class ItemSalesReportRepository {
	
	public function getResults($fromDate, $toDate)
	{
		$results = array();

		$items = InvoiceItem::byDateRange($fromDate, $toDate)->orderBy('name')->get();

		foreach ($items as $item)
		{
			$results[$item->name]['items'][] = array(
				'client_name'    => $item->invoice->client->name,
				'invoice_number' => $item->invoice->number,
				'date'           => $item->invoice->formatted_created_at,
				'price'          => $item->formatted_price,
				'quantity'       => $item->formatted_quantity,
				'total'          => $item->amount->formatted_total
			);

			if (isset($results[$item->name]['totals']))
			{
				$results[$item->name]['totals']['quantity'] += $item->quantity;
				$results[$item->name]['totals']['total']    += $item->amount->total;
			}
			else
			{
				$results[$item->name]['totals']['quantity'] = $item->quantity;
				$results[$item->name]['totals']['total']    = $item->amount->total;
			}
		}

		foreach ($results as $key => $result)
		{
			$results[$key]['totals']['quantity'] = NumberFormatter::format($results[$key]['totals']['quantity']);
			$results[$key]['totals']['total']    = CurrencyFormatter::format($results[$key]['totals']['total']);
		}

		return $results;
	}

}