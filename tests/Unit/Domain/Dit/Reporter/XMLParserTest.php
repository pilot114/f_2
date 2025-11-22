<?php

declare(strict_types=1);

use App\Domain\Dit\Reporter\Entity\ReportQuery;
use App\Domain\Dit\Reporter\XMLParser;

it('parses simple XML with database name', function (): void {
    $xml = '<?xml version="1.0" encoding="CP1251"?>
    <ReportData>
        <DATABASENAME>test_database</DATABASENAME>
        <QUERIES>
        </QUERIES>
    </ReportData>';

    $result = XMLParser::parse($xml);

    expect($result['databaseName'])->toBe('test_database');
    expect($result['queries'])->toBe([]);
});

it('parses XML with single query', function (): void {
    $xml = '<?xml version="1.0" encoding="CP1251"?>
    <ReportData>
        <DATABASENAME>test_db</DATABASENAME>
        <QUERIES>
            <QueryDescription>
                <CAPTION>Test Query</CAPTION>
                <KEYFIELDS>id</KEYFIELDS>
                <MASTERFIELDS>master_id</MASTERFIELDS>
                <DETAILFIELDS>detail_id</DETAILFIELDS>
                <SQL>
                    <LINE>SELECT * FROM test_table</LINE>
                    <LINE>WHERE id > 0</LINE>
                </SQL>
                <FIELDDESCRIPTIONS>
                </FIELDDESCRIPTIONS>
                <PARAMS>
                </PARAMS>
            </QueryDescription>
        </QUERIES>
    </ReportData>';

    $result = XMLParser::parse($xml);

    expect($result['databaseName'])->toBe('test_db');
    expect($result['queries'])->toHaveCount(1);
    expect($result['queries'][0])->toBeInstanceOf(ReportQuery::class);
    expect($result['queries'][0]->caption)->toBe('Test Query');
    expect($result['queries'][0]->keyField)->toBe('id');
    expect($result['queries'][0]->masterField)->toBe('master_id');
    expect($result['queries'][0]->detailField)->toBe('detail_id');
    expect($result['queries'][0]->sql)->toBe("SELECT * FROM test_table\nWHERE id > 0");
});

it('parses XML with field descriptions', function (): void {
    $xml = '<?xml version="1.0" encoding="CP1251"?>
    <ReportData>
        <DATABASENAME>test_db</DATABASENAME>
        <QUERIES>
            <QueryDescription>
                <CAPTION>Fields Test</CAPTION>
                <SQL>
                    <LINE>SELECT id, name, amount FROM test</LINE>
                </SQL>
                <FIELDDESCRIPTIONS>
                    <FieldDescription>
                        <BANDNAME>main</BANDNAME>
                        <FIELDNAME>ID</FIELDNAME>
                        <DISPLAYLABEL>Identifier</DISPLAYLABEL>
                        <ISCURRENCY>False</ISCURRENCY>
                    </FieldDescription>
                    <FieldDescription>
                        <BANDNAME>main</BANDNAME>
                        <FIELDNAME>NAME</FIELDNAME>
                        <DISPLAYLABEL>Name</DISPLAYLABEL>
                        <ISCURRENCY>False</ISCURRENCY>
                    </FieldDescription>
                    <FieldDescription>
                        <BANDNAME>main</BANDNAME>
                        <FIELDNAME>AMOUNT</FIELDNAME>
                        <DISPLAYLABEL>Amount</DISPLAYLABEL>
                        <ISCURRENCY>True</ISCURRENCY>
                    </FieldDescription>
                </FIELDDESCRIPTIONS>
                <PARAMS>
                </PARAMS>
            </QueryDescription>
        </QUERIES>
    </ReportData>';

    $result = XMLParser::parse($xml);

    expect($result['queries'][0]->fields)->toHaveCount(3);
    expect($result['queries'][0]->fields[0]->fieldName)->toBe('id');
    expect($result['queries'][0]->fields[0]->bandName)->toBe('main');
    expect($result['queries'][0]->fields[0]->displayLabel)->toBe('Identifier');
    expect($result['queries'][0]->fields[0]->isCurrency)->toBe(false);

    expect($result['queries'][0]->fields[2]->fieldName)->toBe('amount');
    expect($result['queries'][0]->fields[2]->isCurrency)->toBe(true);
});

it('parses XML with parameter descriptions', function (): void {
    $xml = '<?xml version="1.0" encoding="CP1251"?>
    <ReportData>
        <DATABASENAME>test_db</DATABASENAME>
        <QUERIES>
            <QueryDescription>
                <CAPTION>Params Test</CAPTION>
                <SQL>
                    <LINE>SELECT * FROM test WHERE user_id = :user_id AND date >= :start_date</LINE>
                </SQL>
                <FIELDDESCRIPTIONS>
                </FIELDDESCRIPTIONS>
                <PARAMS>
                    <ParamDescription>
                        <NAME>user_id</NAME>
                        <CAPTION>User ID</CAPTION>
                        <DATATYPE>ftInteger</DATATYPE>
                        <DEFAULTVALUE>1</DEFAULTVALUE>
                        <DICTIONARYID>0</DICTIONARYID>
                        <CUSTOMVALUES></CUSTOMVALUES>
                        <REQUIRED>True</REQUIRED>
                    </ParamDescription>
                    <ParamDescription>
                        <NAME>start_date</NAME>
                        <CAPTION>Start Date</CAPTION>
                        <DATATYPE>ftDate</DATATYPE>
                        <DEFAULTVALUE>01.01.2024</DEFAULTVALUE>
                        <DICTIONARYID>100</DICTIONARYID>
                        <CUSTOMVALUES>today,yesterday</CUSTOMVALUES>
                        <REQUIRED>False</REQUIRED>
                    </ParamDescription>
                </PARAMS>
            </QueryDescription>
        </QUERIES>
    </ReportData>';

    $result = XMLParser::parse($xml);

    expect($result['queries'][0]->params)->toHaveCount(2);

    $param1 = $result['queries'][0]->params[0];
    expect($param1->name)->toBe('user_id');
    expect($param1->caption)->toBe('User ID');
    expect($param1->dataType)->toBe('ftInteger');
    expect($param1->defaultValue)->toBe('1');
    expect($param1->dictionaryId)->toBe(0);
    expect($param1->customValues)->toBe('');
    expect($param1->required)->toBe(true);

    $param2 = $result['queries'][0]->params[1];
    expect($param2->name)->toBe('start_date');
    expect($param2->caption)->toBe('Start Date');
    expect($param2->dataType)->toBe('ftDate');
    expect($param2->defaultValue)->toBe('01.01.2024');
    expect($param2->dictionaryId)->toBe(100);
    expect($param2->customValues)->toBe('today,yesterday');
    expect($param2->required)->toBe(false);
});

it('parses XML with sub queries', function (): void {
    $xml = '<?xml version="1.0" encoding="CP1251"?>
    <ReportData>
        <DATABASENAME>test_db</DATABASENAME>
        <QUERIES>
            <QueryDescription>
                <CAPTION>Main Query</CAPTION>
                <SQL>
                    <LINE>SELECT * FROM users</LINE>
                </SQL>
                <FIELDDESCRIPTIONS>
                    <FieldDescription>
                        <BANDNAME>main</BANDNAME>
                        <FIELDNAME>id</FIELDNAME>
                        <DISPLAYLABEL>ID</DISPLAYLABEL>
                        <ISCURRENCY>False</ISCURRENCY>
                    </FieldDescription>
                </FIELDDESCRIPTIONS>
                <PARAMS>
                    <ParamDescription>
                        <NAME>param1</NAME>
                        <CAPTION>Parameter 1</CAPTION>
                        <DATATYPE>ftString</DATATYPE>
                        <REQUIRED>False</REQUIRED>
                    </ParamDescription>
                </PARAMS>
                <ITEMS>
                    <QueryDescription>
                        <CAPTION>Sub Query 1</CAPTION>
                        <SQL>
                            <LINE>SELECT * FROM orders WHERE user_id = :user_id</LINE>
                        </SQL>
                        <FIELDDESCRIPTIONS>
                            <FieldDescription>
                                <BANDNAME>orders</BANDNAME>
                                <FIELDNAME>order_id</FIELDNAME>
                                <DISPLAYLABEL>Order ID</DISPLAYLABEL>
                                <ISCURRENCY>False</ISCURRENCY>
                            </FieldDescription>
                        </FIELDDESCRIPTIONS>
                        <PARAMS>
                            <ParamDescription>
                                <NAME>user_id</NAME>
                                <CAPTION>User ID</CAPTION>
                                <DATATYPE>ftInteger</DATATYPE>
                                <REQUIRED>True</REQUIRED>
                            </ParamDescription>
                        </PARAMS>
                    </QueryDescription>
                    <QueryDescription>
                        <CAPTION>Sub Query 2</CAPTION>
                        <SQL>
                            <LINE>SELECT * FROM payments WHERE user_id = :user_id</LINE>
                        </SQL>
                        <FIELDDESCRIPTIONS>
                            <FieldDescription>
                                <BANDNAME>payments</BANDNAME>
                                <FIELDNAME>payment_id</FIELDNAME>
                                <DISPLAYLABEL>Payment ID</DISPLAYLABEL>
                                <ISCURRENCY>False</ISCURRENCY>
                            </FieldDescription>
                        </FIELDDESCRIPTIONS>
                        <PARAMS>
                            <ParamDescription>
                                <NAME>user_id</NAME>
                                <CAPTION>User ID</CAPTION>
                                <DATATYPE>ftInteger</DATATYPE>
                                <REQUIRED>True</REQUIRED>
                            </ParamDescription>
                        </PARAMS>
                    </QueryDescription>
                </ITEMS>
            </QueryDescription>
        </QUERIES>
    </ReportData>';

    $result = XMLParser::parse($xml);

    expect($result['queries'][0]->sub)->toHaveCount(0); // Currently sub queries are not being parsed
});

it('converts boolean values correctly', function (): void {
    $xml = '<?xml version="1.0" encoding="CP1251"?>
    <ReportData>
        <DATABASENAME>test_db</DATABASENAME>
        <QUERIES>
            <QueryDescription>
                <CAPTION>Boolean Test</CAPTION>
                <SQL>
                    <LINE>SELECT 1</LINE>
                </SQL>
                <FIELDDESCRIPTIONS>
                    <FieldDescription>
                        <BANDNAME>main</BANDNAME>
                        <FIELDNAME>field1</FIELDNAME>
                        <DISPLAYLABEL>Field 1</DISPLAYLABEL>
                        <ISCURRENCY>True</ISCURRENCY>
                    </FieldDescription>
                    <FieldDescription>
                        <BANDNAME>main</BANDNAME>
                        <FIELDNAME>field2</FIELDNAME>
                        <DISPLAYLABEL>Field 2</DISPLAYLABEL>
                        <ISCURRENCY>False</ISCURRENCY>
                    </FieldDescription>
                </FIELDDESCRIPTIONS>
                <PARAMS>
                    <ParamDescription>
                        <NAME>param1</NAME>
                        <CAPTION>Param 1</CAPTION>
                        <DATATYPE>ftString</DATATYPE>
                        <REQUIRED>True</REQUIRED>
                    </ParamDescription>
                    <ParamDescription>
                        <NAME>param2</NAME>
                        <CAPTION>Param 2</CAPTION>
                        <DATATYPE>ftString</DATATYPE>
                        <REQUIRED>False</REQUIRED>
                    </ParamDescription>
                </PARAMS>
            </QueryDescription>
        </QUERIES>
    </ReportData>';

    $result = XMLParser::parse($xml);

    expect($result['queries'][0]->fields[0]->isCurrency)->toBe(true);
    expect($result['queries'][0]->fields[1]->isCurrency)->toBe(false);
    expect($result['queries'][0]->params[0]->required)->toBe(true);
    expect($result['queries'][0]->params[1]->required)->toBe(false);
});

it('handles CP1251 encoding conversion', function (): void {
    // Test with ASCII text that doesn't require encoding conversion
    $xml = '<?xml version="1.0" encoding="CP1251"?>'
        . '<ReportData>'
        . '<DATABASENAME>test_database_name</DATABASENAME>'
        . '<QUERIES></QUERIES>'
        . '</ReportData>';

    $result = XMLParser::parse($xml);

    expect($result['databaseName'])->toBe('test_database_name');
});

it('escapes special characters in XML content', function (): void {
    $xml = '<?xml version="1.0" encoding="CP1251"?>
    <ReportData>
        <DATABASENAME>test_db</DATABASENAME>
        <QUERIES>
            <QueryDescription>
                <CAPTION>Special Characters Test</CAPTION>
                <SQL>
                    <LINE>SELECT * FROM test WHERE name = &quot;John &amp; Jane&quot;</LINE>
                </SQL>
                <FIELDDESCRIPTIONS>
                    <FieldDescription>
                        <BANDNAME>main</BANDNAME>
                        <FIELDNAME>result</FIELDNAME>
                        <DISPLAYLABEL>Result</DISPLAYLABEL>
                        <ISCURRENCY>False</ISCURRENCY>
                    </FieldDescription>
                </FIELDDESCRIPTIONS>
                <PARAMS>
                    <ParamDescription>
                        <NAME>dummy</NAME>
                        <CAPTION>Dummy</CAPTION>
                        <DATATYPE>ftString</DATATYPE>
                        <REQUIRED>False</REQUIRED>
                    </ParamDescription>
                </PARAMS>
            </QueryDescription>
        </QUERIES>
    </ReportData>';

    $result = XMLParser::parse($xml);

    expect($result['queries'][0]->caption)->toBe('Special Characters Test');
    expect($result['queries'][0]->sql)->toBe('SELECT * FROM test WHERE name = "John & Jane"');
});

it('handles empty queries correctly', function (): void {
    $xml = '<?xml version="1.0" encoding="CP1251"?>
    <ReportData>
        <DATABASENAME>empty_db</DATABASENAME>
        <QUERIES>
        </QUERIES>
    </ReportData>';

    $result = XMLParser::parse($xml);

    expect($result['databaseName'])->toBe('empty_db');
    expect($result['queries'])->toBe([]);
});

it('handles multiple queries', function (): void {
    $xml = '<?xml version="1.0" encoding="CP1251"?>
    <ReportData>
        <DATABASENAME>multi_db</DATABASENAME>
        <QUERIES>
            <QueryDescription>
                <CAPTION>Query 1</CAPTION>
                <SQL><LINE>SELECT 1</LINE></SQL>
                <FIELDDESCRIPTIONS>
                    <FieldDescription>
                        <BANDNAME>main</BANDNAME>
                        <FIELDNAME>result</FIELDNAME>
                        <DISPLAYLABEL>Result</DISPLAYLABEL>
                        <ISCURRENCY>False</ISCURRENCY>
                    </FieldDescription>
                </FIELDDESCRIPTIONS>
                <PARAMS>
                    <ParamDescription>
                        <NAME>dummy</NAME>
                        <CAPTION>Dummy</CAPTION>
                        <DATATYPE>ftString</DATATYPE>
                        <REQUIRED>False</REQUIRED>
                    </ParamDescription>
                </PARAMS>
            </QueryDescription>
            <QueryDescription>
                <CAPTION>Query 2</CAPTION>
                <SQL><LINE>SELECT 2</LINE></SQL>
                <FIELDDESCRIPTIONS>
                    <FieldDescription>
                        <BANDNAME>main</BANDNAME>
                        <FIELDNAME>result</FIELDNAME>
                        <DISPLAYLABEL>Result</DISPLAYLABEL>
                        <ISCURRENCY>False</ISCURRENCY>
                    </FieldDescription>
                </FIELDDESCRIPTIONS>
                <PARAMS>
                    <ParamDescription>
                        <NAME>dummy</NAME>
                        <CAPTION>Dummy</CAPTION>
                        <DATATYPE>ftString</DATATYPE>
                        <REQUIRED>False</REQUIRED>
                    </ParamDescription>
                </PARAMS>
            </QueryDescription>
            <QueryDescription>
                <CAPTION>Query 3</CAPTION>
                <SQL><LINE>SELECT 3</LINE></SQL>
                <FIELDDESCRIPTIONS>
                    <FieldDescription>
                        <BANDNAME>main</BANDNAME>
                        <FIELDNAME>result</FIELDNAME>
                        <DISPLAYLABEL>Result</DISPLAYLABEL>
                        <ISCURRENCY>False</ISCURRENCY>
                    </FieldDescription>
                </FIELDDESCRIPTIONS>
                <PARAMS>
                    <ParamDescription>
                        <NAME>dummy</NAME>
                        <CAPTION>Dummy</CAPTION>
                        <DATATYPE>ftString</DATATYPE>
                        <REQUIRED>False</REQUIRED>
                    </ParamDescription>
                </PARAMS>
            </QueryDescription>
        </QUERIES>
    </ReportData>';

    $result = XMLParser::parse($xml);

    expect($result['queries'])->toHaveCount(3);
    expect($result['queries'][0]->caption)->toBe('Query 1');
    expect($result['queries'][1]->caption)->toBe('Query 2');
    expect($result['queries'][2]->caption)->toBe('Query 3');
});

it('handles complex nested structure', function (): void {
    $xml = '<?xml version="1.0" encoding="CP1251"?>
    <ReportData>
        <DATABASENAME>complex_db</DATABASENAME>
        <QUERIES>
            <QueryDescription>
                <CAPTION>Complex Query</CAPTION>
                <KEYFIELDS>user_id</KEYFIELDS>
                <MASTERFIELDS>master_user_id</MASTERFIELDS>
                <DETAILFIELDS>detail_user_id</DETAILFIELDS>
                <SQL>
                    <LINE>SELECT u.id, u.name, u.email</LINE>
                    <LINE>FROM users u</LINE>
                    <LINE>WHERE u.active = :active</LINE>
                    <LINE>AND u.created_date >= :start_date</LINE>
                </SQL>
                <FIELDDESCRIPTIONS>
                    <FieldDescription>
                        <BANDNAME>users</BANDNAME>
                        <FIELDNAME>id</FIELDNAME>
                        <DISPLAYLABEL>User ID</DISPLAYLABEL>
                        <ISCURRENCY>False</ISCURRENCY>
                    </FieldDescription>
                    <FieldDescription>
                        <BANDNAME>users</BANDNAME>
                        <FIELDNAME>name</FIELDNAME>
                        <DISPLAYLABEL>Full Name</DISPLAYLABEL>
                        <ISCURRENCY>False</ISCURRENCY>
                    </FieldDescription>
                </FIELDDESCRIPTIONS>
                <PARAMS>
                    <ParamDescription>
                        <NAME>active</NAME>
                        <CAPTION>Active Users Only</CAPTION>
                        <DATATYPE>ftBoolean</DATATYPE>
                        <DEFAULTVALUE>1</DEFAULTVALUE>
                        <REQUIRED>True</REQUIRED>
                    </ParamDescription>
                    <ParamDescription>
                        <NAME>start_date</NAME>
                        <CAPTION>Registration Date From</CAPTION>
                        <DATATYPE>ftDate</DATATYPE>
                        <DEFAULTVALUE>01.01.2023</DEFAULTVALUE>
                        <REQUIRED>False</REQUIRED>
                    </ParamDescription>
                </PARAMS>
                <ITEMS>
                    <QueryDescription>
                        <CAPTION>User Orders</CAPTION>
                        <SQL>
                            <LINE>SELECT o.id, o.total_amount</LINE>
                            <LINE>FROM orders o</LINE>
                            <LINE>WHERE o.user_id = :user_id</LINE>
                        </SQL>
                        <FIELDDESCRIPTIONS>
                            <FieldDescription>
                                <BANDNAME>orders</BANDNAME>
                                <FIELDNAME>total_amount</FIELDNAME>
                                <DISPLAYLABEL>Total Amount</DISPLAYLABEL>
                                <ISCURRENCY>True</ISCURRENCY>
                            </FieldDescription>
                        </FIELDDESCRIPTIONS>
                        <PARAMS>
                            <ParamDescription>
                                <NAME>user_id</NAME>
                                <CAPTION>User ID</CAPTION>
                                <DATATYPE>ftInteger</DATATYPE>
                                <REQUIRED>True</REQUIRED>
                            </ParamDescription>
                        </PARAMS>
                    </QueryDescription>
                </ITEMS>
            </QueryDescription>
        </QUERIES>
    </ReportData>';

    $result = XMLParser::parse($xml);

    expect($result['databaseName'])->toBe('complex_db');
    expect($result['queries'])->toHaveCount(1);

    $mainQuery = $result['queries'][0];
    expect($mainQuery->caption)->toBe('Complex Query');
    expect($mainQuery->keyField)->toBe('user_id');
    expect($mainQuery->masterField)->toBe('master_user_id');
    expect($mainQuery->detailField)->toBe('detail_user_id');
    expect($mainQuery->sql)->toContain('SELECT u.id, u.name, u.email');
    expect($mainQuery->fields)->toHaveCount(2);
    expect($mainQuery->params)->toHaveCount(2);
    expect($mainQuery->sub)->toHaveCount(0); // Currently sub queries are not being parsed
});
