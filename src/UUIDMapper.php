<?php
namespace SuiteMapper;

/**
 * Class UUIDMapper
 */
class UUIDMapper
{
	/**
	 * @param integer $dynamoId
	 * @return bool|string
	 */
	public function getSuiteIdByDynamoId($dynamoId)
	{
		$result = $this->db->query("SELECT suite_uuid FROM id_mapping WHERE dynamo_uuid = '". $this->db->escape($dynamoId) ."'");
		if ($result) {
			$row = $result->fetch_assoc();

			return $row['suite_uuid'];
		}
		return false;
	}
}