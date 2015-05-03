<?phpif(!class_exists ("glab_wdal")){
class glab_wdal {

	var $wpdb;
	function CL_WDAL($instance) {
		$this->wpdb = $instance;
	}

	/*
	 name:w_sql_retrieve
	 purpose:retrive data from database according to the query
	 */
	function w_sql_retrieve($sql_query = '') {
		$res = array();
		if ($sql_query != '') {
			$res = $this->wpdb->get_results($sql_query);
			return $res;
		} else {
			return $res;
		}
	}

	/*
	 name:clinic_sql_insert
	 purpose:insert data to database
	 author:exporter2022
	 email:exporter2022@gmail.com
	 */
	function w_sql_insert($sql_query = '') {

		if ($sql_query != '') {
			$query = $this->wpdb->query($sql_query);
			return $this->wpdb->insert_id; //return the id number of db against inserted data
		} else {
			return 0;
		}
	}

	function w_wp_insert($table, $data, $format = '') {
		$this->wpdb->insert($table, $data);
		return $this->wpdb->insert_id;
	}

	/*
	 name:w_sql_update
	 purpose:update data stored in database
	 */

	function clinic_sql_update($table = '', $data = null, $where = null, $format = null, $where_format = null) {
		if ($table != '' && isset($data)) {
			if (isset($format) && !empty($format) && isset($where_format) && !empty($where_format))
				$this->wpdb->update($table, $data, $where, $format, $where_format);
			elseif (isset($format) && !empty($format))
				$this->wpdb->update($table, $data, $where, $format);
			else
				$this->wpdb->update($table, $data, $where);
			return $this->wpdb->insert_id;
		} else {
			return 0;
		}
	}

	/*
	 name:w_sql_queries
	 purpose:perform any kind of operation using this function
	 */

	function w_sql_queries($sql_query = '') {
		if ($sql_query != '' && isset($sql_query)) {
			return $this->wpdb->query($sql_query);
		} else {
			return 0;
		}
	}
}}
?>