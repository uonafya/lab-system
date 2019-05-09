<?php

namespace App;

class Datatable
{


	public static function limit($request, &$model)
	{
		$start = $request->input('start');
		$length = $request->input('length');
		if($start && $length != -1) $model->limit($length)->offset($start);
	}

	public static function order($request, &$model, $db_columns)
	{
		$order = $request->input('order');
		$columns = $request->input('columns');
		$dtColumns = array_column( $db_columns, 'dt' );

		if($order && count($order)){
			foreach ($order as $key => $value) {
				$columnIdx = intval($value['column']);
				$requestColumn = $columns[$columnIdx];

				$columnIdx = array_search( $requestColumn['data'], $dtColumns );
				$column_array = $db_columns[ $columnIdx ];

				if ( $requestColumn['orderable'] && $requestColumn['orderable'] == 'true' ) {
					$dir = $value['dir'] === 'asc' ? 'ASC' : 'DESC';
					$model->orderBy($column_array['db'], $dir);
				}
			}
		}
	}

	public static function filter($request, &$model, $db_columns, $table_name=null)
	{
		$search = $request->input('search');
		$columns = $request->input('columns');
		$dtColumns = array_column( $db_columns, 'dt' );

		$str = '';
		$or_query = [];

		if($search && $search['value'] != '') $str = "'%" . $search['value'] . "%'";

		foreach ($columns as $key => $requestColumn) {
			$columnIdx = array_search( $requestColumn['data'], $dtColumns );
			$column_array = $db_columns[ $columnIdx ];

			$column_name = '';
			if($table_name) $column_name = $table_name . '.';
			$column_name .= '.' . $column_array['db'];

			if ( $search && $search['value'] != '' && $requestColumn['searchable'] && $requestColumn['searchable'] == 'true' ){
				$or_query[] = $column_name . " LIKE {$str}";
			}

			// Individual column filtering
			$ind_str = $requestColumn['search']['value'];
			if($ind_str && $ind_str != '') $model->where($column_name, 'LIKE', "%{$ind_str}%");
		}

		if(count($or_query)){
			$where = '('.implode(' OR ', $or_query).')';
			$model->whereRaw($where);
		}
	}
}
