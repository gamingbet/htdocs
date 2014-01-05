<?php

/**
 * Tables Class
 * Author: Kamil Piechaczek
 * Date: 04-07-2013
 * Description: Bets between users
 * Version: 1.0
 * License: Creative Commons
 */
 
class Tables {

	/**
	 * Table Status - Enabled
	 */
	const ENABLED = 'open';
	
	/**
	 * Table Status - Disabled
	 */
	const DISABLED = 'finish';
	
	/**
	 * Add Symbol
	 */
	const ADD = '+';
	
	/**
	 * Subtract Symbol
	 */
	const SUBTRACT = '-';
	
	/**
	 * DB Connect identify
	 */
	private $pdo;
	
	/**
	 * Login User ID
	 */
	private $customerId;
	
	/**
	 * Counter custom tables
	 */
	private $countTables;
	
	/**
	 * Tables Construct
	 */
	public function __construct() {
		$this->pdo = DB::getConnect();
	}
	
	// ==== CORE / LOGICS ====
	
	/**
	 * Get All Tables From Match by Match ID
	 *
	 * @return (array) CustomersTables
	 */
	public function getTablesByMatchID($pMatchId, $pStatus) {
		$sql = "
			SELECT 
				t.id, 
				t.course,
				t.ownerOption,
				t.player1Id,
				t.player2Id,
				u1.nick as player1, 
				u2.nick as player2,
				bt.id as betId,
				l.`label-pl` as langpl,
				l.`label-en` as langen,
				m.start as begin
			FROM
				tables as t
			JOIN
				users as u1
			ON
				t.player1Id = u1.id
			LEFT JOIN
				users as u2
			ON
				t.player2Id = u2.id
			JOIN 
				bettypes as bt
			ON
				t.betType = bt.id
			JOIN
				langs as l
			ON
				l.category = 'bets' AND
				l.label = bt.type
			JOIN
				matches as m
			ON
				t.matchId = m.id
			WHERE
				t.matchId = ".$pMatchId." AND
				t.status = '". $pStatus ."'
			ORDER BY 
				t.player2Id ASC,
				t.id DESC
		";
		
		$query = $this->pdo->query($sql);		
		$result = array();
		
		$this->countTables = 0;
		while($row = $query->fetch()) {
			// Remove numeric index
			foreach($row as $key => $value){
				if(is_numeric($key)){
					unset($row[$key]);
				}
			}
			$result[] = $row;
			++$this->countTables;
		}
		
		return $result;
	}
	
	/**
	 * Change Customer Credits
	 *
	 * @return (boolean)
	 */
	public function changeCustomerCreditsByCustomerId($pAction, $pCredits, $pCustomerId = NULL) {
		if($pCustomerId == NULL) {
			$pCustomerId = $this->getCustomerId();
		}
		
		$sql = "
			UPDATE
				users
			SET
				credits = (credits ".$pAction." ".$pCredits.")
			WHERE
				id = ".$pCustomerId;
		
		return $this->pdo->exec($sql);
	}
	
	/**
	 * Save Customer ID to Table by TableID
	 *
	 * @return (boolean)
	 */
	public function saveCustomerToTable($pTable, $pCustomerId = NULL) {
		if($pCustomerId == NULL) {
			$pCustomerId = $this->getCustomerId();
		}
		
		$sql = "
			UPDATE
				tables
			SET
				player2Id = ".$pCustomerId."
			WHERE
				id = ". $pTable;
			
		return $this->pdo->exec($sql);
	}
	
	/**
	 * Remove Customer ID to Table by TableID
	 *
	 * @return (boolean)
	 */
	public function removeCustomerToTable($pTable) {
		$sql = "
			UPDATE
				tables
			SET
				player2Id = 0
			WHERE
				id = ". $pTable;
			
		return $this->pdo->exec($sql);
	}
	
	/**
	 * Check Customer is assigned to Table by TableID
	 *
	 * @return (boolean)
	 */
	public function isCustomerAssignToTable($pCustomerId, $pTable) {
		$sql = "
			SELECT
				count(id)
			FROM
				tables
			WHERE
				player2Id = ".$pCustomerId." AND
				id = ". $pTable;
		
		$query = $this->pdo->query($sql)->fetch();
		
		if($query[0] == '0') {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * Check allowed bet match by MatchId
	 *
	 * @return (boolean) 
	 */
	public function isAllowBetMatch($pMatchId) {
		$sql = "
			SELECT
				( UNIX_TIMESTAMP(start) - UNIX_TIMESTAMP(NOW()) ) AS `diff`
			FROM
				matches
			WHERE
				id = ".$pMatchId;
		
		$query = $this->pdo->query($sql)->fetch();
		
		if( $query['diff'] > 0) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Check allowed create custom bet by Customer Id
	 *
	 * @return (boolean)
	 */
	public function isAllowCreateBet($pMoney, $pCustomerId = NULL) {
	
		if($pCustomerId == NULL) {
			$pCustomerId = $this->getCustomerId();
		}
		
		$sql = "
			SELECT
				id
			FROM
				users
			WHERE
				credits >= ".$pMoney." AND
				id = ".$pCustomerId;
				
		$query = $this->pdo->query($sql)->fetch();
		
		if($query == NULL) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * Check is Bet Type attach in 
	 *
	 * @return (boolean)
	 */
	public function isBetTypeInBetsMatch($pBetType, $pMatchId) {
		$sql = "
			SELECT
				count(id)
			FROM
				bets
			WHERE
				typeId = ".$pBetType." AND
				matchId = ".$pMatchId;
		
		$query = $this->pdo->query($sql)->fetch();
		
		if($query[0] == '0') {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * Check owner Table
	 *
	 * @return (boolean)
	 */
	public function isOwnerTable($pTableId) {
		$sql = "
			SELECT
				id
			FROM
				tables
			WHERE
				id = ".$pTableId." AND
				createdBy = ".$this->getCustomerId();
				
		$query = $this->pdo->query($sql)->fetch();
		
		if($query == NULL) {
			return false;
		} else {
			return true;
		}		
	}
	
	/**
	 * Check is isset Table by TableId
	 *
	 * @return (int) Course
	 */
	public function isIssetTable($pTableId) {
		$sql = "
			SELECT
				count(id)
			FROM 
				tables
			WHERE
				id = ".$pTableId;
		
		$query = $this->pdo->query($sql)->fetch();
		
		if($query[0] == '1') {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Check is free place in table by TableId
	 *
	 * @return (int) Course
	 */
	public function canJoinToTable($pTableId) {
		$sql = "
			SELECT
				count(id)
			FROM 
				tables
			WHERE
				player2Id = 0 AND
				id = ".$pTableId;

		$query = $this->pdo->query($sql)->fetch();
		
		if($query[0] == '1') {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Remove Table by TableId
	 *
	 * @return (boolean)
	 */
	public function removeTable($pTableId) {
		$sql = "
			DELETE FROM
				tables
			WHERE
				id = ".$pTableId;
		
		return $this->pdo->exec($sql);
	}
	
	// ==== GETTERS / SETTERS ====
	
	/**
	 * Set login Customer ID
	 *
	 * @return (Tables) $this
	 */
	public function setCustomerId($pCustomerId) {
		$this->customerId = $pCustomerId;
		return $this;
	}
	
	/**
	 * Get login Customer Id
	 *
	 * @return (int) CustomerId
	 */
	public function getCustomerId() {
		return $this->customerId;
	}
	
	/**
	 * Get number of custom Tables
	 *
	 * @return (int) CountTables
	 */
	public function getCountTables() {
		return $this->countTables;
	}
	
	/**
	 * Get number of custom Tables by Customer Id
	 *
	 * @return (int) CountTables
	 */
	public function getCountUserTables() {
		$sql = "
			SELECT
				count(id)
			FROM 
				tables
			WHERE
				status = '".self::ENABLED."' AND
				createdBy = ".$this->getCustomerId();
		
		$query = $this->pdo->query($sql)->fetch();
		
		return $query[0];
	}
	
	/**
	 * Get Course in bet by TableId
	 *
	 * @return (int) Course
	 */
	public function getCourseByTableId($pTableId) {
		$sql = "
			SELECT
				course
			FROM 
				tables
			WHERE
				id = ".$pTableId;
		
		$query = $this->pdo->query($sql)->fetch();
		
		return $query[0];
	}
	
	/**
	 * Get Second Player in Table by TableId
	 *
	 * @return (int) Player2Id
	 */
	public function getSecondPlayerInTable($pTableId) {
		$sql = "
			SELECT
				player2Id
			FROM 
				tables
			WHERE
				id = ".$pTableId;
		
		$query = $this->pdo->query($sql)->fetch();
		
		return $query[0];
	}
	
}

?>