#
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content (
  tx_masterrecord_master int(2) DEFAULT NULL,
  tx_masterrecord_instances int(2) DEFAULT NULL,
  tx_masterrecord_instanceof int(11) DEFAULT NULL,
  tx_masterrecord_group varchar(255) DEFAULT '' NOT NULL,
  KEY tx_masterrecord_master (tx_masterrecord_master),
  KEY tx_masterrecord_instanceof (tx_masterrecord_instanceof),
);
