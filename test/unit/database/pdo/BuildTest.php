<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Build;
use system\database\DatabaseException;

class BuildTest extends TestCase
{
    /**
     * 测试 wherePlaceholder 方法 - 基本功能
     */
    public function testWherePlaceholderBasic()
    {
        $wheres = [
            ['id', '=', 1],
            ['name', 'LIKE', '%test%']
        ];
        
        $result = Build::wherePlaceholder($wheres);
        $expected = 'id = :where_0 name LIKE :where_1';
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 wherePlaceholder 方法 - 带有逻辑运算符
     */
    public function testWherePlaceholderWithLogic()
    {
        $wheres = [
            ['id', '=', 1, 'and'],
            ['name', 'LIKE', '%test%', 'or'],
            ['age', '>', 18]
        ];
        
        $result = Build::wherePlaceholder($wheres);
        $expected = 'id = :where_0 AND name LIKE :where_1 OR age > :where_2';
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 wherePlaceholder 方法 - IN 操作符
     */
    public function testWherePlaceholderWithIn()
    {
        $wheres = [
            ['id', 'IN', [1, 2, 3]]
        ];
        
        $result = Build::wherePlaceholder($wheres);
        $expected = 'id IN (:where_0_0,:where_0_1,:where_0_2)';
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 wherePlaceholder 方法 - BETWEEN 操作符
     */
    public function testWherePlaceholderWithBetween()
    {
        $wheres = [
            ['age', 'BETWEEN', [18, 30]]
        ];
        
        $result = Build::wherePlaceholder($wheres);
        $expected = 'age BETWEEN :where_0_0 AND :where_0_1';
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 wherePlaceholder 方法 - 空条件
     */
    public function testWherePlaceholderEmpty()
    {
        $wheres = [];
        $result = Build::wherePlaceholder($wheres);
        $this->assertEquals('', $result);
    }

    /**
     * 测试 wherePlaceholder 方法 - 条件格式错误
     */
    public function testWherePlaceholderInvalidFormat()
    {
        $wheres = [
            ['id', '='] // 缺少值
        ];
        
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('buildWhere Condition Format Is Wrong.');
        Build::wherePlaceholder($wheres);
    }

    /**
     * 测试 wherePlaceholder 方法 - 字段名不是字符串
     */
    public function testWherePlaceholderInvalidField()
    {
        $wheres = [
            [1, '=', 1] // 字段名是数字
        ];
        
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('buildWhere Key Must Be String. 0');
        Build::wherePlaceholder($wheres);
    }

    /**
     * 测试 wherePlaceholder 方法 - 操作符不是字符串
     */
    public function testWherePlaceholderInvalidOperator()
    {
        $wheres = [
            ['id', 1, 1] // 操作符是数字
        ];
        
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('buildWhere Operator Must Be String. id');
        Build::wherePlaceholder($wheres);
    }

    /**
     * 测试 wherePlaceholder 方法 - 值不是标量或数组
     */
    public function testWherePlaceholderInvalidValue()
    {
        $wheres = [
            ['id', '=', new stdClass()] // 值是对象
        ];
        
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('buildWhere Value Condition Format Is Wrong. id');
        Build::wherePlaceholder($wheres);
    }

    /**
     * 测试 wherePlaceholder 方法 - 逻辑运算符无效
     */
    public function testWherePlaceholderInvalidLogic()
    {
        $wheres = [
            ['id', '=', 1, 'invalid'] // 无效的逻辑运算符
        ];
        
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('buildWhere Logic Condition Format Is Wrong. id');
        Build::wherePlaceholder($wheres);
    }

    /**
     * 测试 wherePlaceholder 方法 - 操作符无效
     */
    public function testWherePlaceholderInvalidOperatorType()
    {
        $wheres = [
            ['id', 'INVALID', 1] // 无效的操作符
        ];
        
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('buildWhere Operator Is Wrong. id');
        Build::wherePlaceholder($wheres);
    }

    /**
     * 测试 wherePlaceholder 方法 - IN 操作符值不是数组
     */
    public function testWherePlaceholderInvalidInValue()
    {
        $wheres = [
            ['id', 'IN', 1] // IN 操作符值不是数组
        ];
        
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('buildWhere In Operator Value Must Be Array With At Least One Element. id');
        Build::wherePlaceholder($wheres);
    }

    /**
     * 测试 wherePlaceholder 方法 - IN 操作符值数组为空
     */
    public function testWherePlaceholderEmptyInValue()
    {
        $wheres = [
            ['id', 'IN', []] // IN 操作符值数组为空
        ];
        
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('buildWhere In Operator Value Must Be Array With At Least One Element. id');
        Build::wherePlaceholder($wheres);
    }

    /**
     * 测试 wherePlaceholder 方法 - IN 操作符值不是字符串或数字
     */
    public function testWherePlaceholderInvalidInValueElements()
    {
        $wheres = [
            ['id', 'IN', [1, new stdClass()]] // IN 操作符值包含对象
        ];
        
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('buildWhere In Operator Value Must Be String Or Numeric. id');
        Build::wherePlaceholder($wheres);
    }

    /**
     * 测试 wherePlaceholder 方法 - BETWEEN 操作符值不是数组
     */
    public function testWherePlaceholderInvalidBetweenValue()
    {
        $wheres = [
            ['age', 'BETWEEN', 18] // BETWEEN 操作符值不是数组
        ];
        
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('buildWhere Between Operator Value Must Be Array With Two Elements. age');
        Build::wherePlaceholder($wheres);
    }

    /**
     * 测试 wherePlaceholder 方法 - BETWEEN 操作符值数组长度不是 2
     */
    public function testWherePlaceholderInvalidBetweenValueLength()
    {
        $wheres = [
            ['age', 'BETWEEN', [18, 30, 40]] // BETWEEN 操作符值数组长度不是 2
        ];
        
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('buildWhere Between Operator Value Must Be Array With Two Elements. age');
        Build::wherePlaceholder($wheres);
    }

    /**
     * 测试 wherePlaceholder 方法 - BETWEEN 操作符值不是数字
     */
    public function testWherePlaceholderInvalidBetweenValueType()
    {
        $wheres = [
            ['age', 'BETWEEN', [18, 'thirty']] // BETWEEN 操作符值不是数字
        ];
        
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('buildWhere Between Operator Value Must Be Numeric. age');
        Build::wherePlaceholder($wheres);
    }

    /**
     * 测试 wherePlaceholder 方法 - 复杂参数测试
     */
    public function testWherePlaceholderComplexParams()
    {
        $wheres = [
            ['id', '=', 1, 'and'],
            ['name', 'LIKE', '%test%', 'or'],
            ['age', 'BETWEEN', [18, 30], 'and'],
            ['status', 'IN', ['active', 'pending'], 'and'],
            ['score', '>=', 80]
        ];
        
        $result = Build::wherePlaceholder($wheres);
        $expected = 'id = :where_0 AND name LIKE :where_1 OR age BETWEEN :where_2_0 AND :where_2_1 AND status IN (:where_3_0,:where_3_1) AND score >= :where_4';
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 wherePlaceholder 方法 - 自定义前缀
     */
    public function testWherePlaceholderCustomPrefix()
    {
        $wheres = [
            ['id', '=', 1],
            ['name', 'LIKE', '%test%']
        ];
        
        $result = Build::wherePlaceholder($wheres, 'custom');
        $expected = 'id = :custom_0 name LIKE :custom_1';
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 wherePlaceholder 方法 - 带有特殊字符的字段名
     */
    public function testWherePlaceholderSpecialFieldNames()
    {
        $wheres = [
            ['user_name', '=', 'test'],
            ['user_age', '>', 18],
            ['user_email', 'LIKE', '%@example.com%']
        ];
        
        $result = Build::wherePlaceholder($wheres);
        $expected = 'user_name = :where_0 user_age > :where_1 user_email LIKE :where_2';
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 wherePlaceholder 方法 - 大量条件
     */
    public function testWherePlaceholderMultipleConditions()
    {
        $wheres = [];
        for ($i = 0; $i < 10; $i++) {
            if ($i < 9) {
                $wheres[] = ['field_' . $i, '=', $i, 'and'];
            } else {
                $wheres[] = ['field_' . $i, '=', $i]; // 最后一个条件不包含逻辑连接符
            }
        }
        
        $result = Build::wherePlaceholder($wheres);
        $expected = '';
        for ($i = 0; $i < 10; $i++) {
            $expected .= 'field_' . $i . ' = :where_' . $i;
            if ($i < 9) {
                $expected .= ' AND ';
            }
        }
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 wherePlaceholderValues 方法 - 基本功能
     */
    public function testWherePlaceholderValuesBasic()
    {
        // 创建一个模拟的 PDOStatement 对象
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->expects($this->exactly(2))
                 ->method('bindValue')
                 ->willReturn(true);
        
        $wheres = [
            ['id', '=', 1],
            ['name', '=', 'test']
        ];
        
        $result = Build::wherePlaceholderValues($mockStmt, $wheres);
        $expected = [
            'where_0' => 1,
            'where_1' => 'test'
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 wherePlaceholderValues 方法 - 空条件
     */
    public function testWherePlaceholderValuesEmpty()
    {
        // 创建一个模拟的 PDOStatement 对象
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->expects($this->never())->method('bindValue');
        
        $wheres = [];
        $result = Build::wherePlaceholderValues($mockStmt, $wheres);
        $this->assertEquals([], $result);
    }

    /**
     * 测试 wherePlaceholderValues 方法 - IN 操作符
     */
    public function testWherePlaceholderValuesWithIn()
    {
        // 创建一个模拟的 PDOStatement 对象
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->expects($this->exactly(3))
                 ->method('bindValue')
                 ->willReturn(true);
        
        $wheres = [
            ['id', 'IN', [1, 2, 3]]
        ];
        
        $result = Build::wherePlaceholderValues($mockStmt, $wheres);
        $expected = [
            'where_0_0' => 1,
            'where_0_1' => 2,
            'where_0_2' => 3
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 wherePlaceholderValues 方法 - BETWEEN 操作符
     */
    public function testWherePlaceholderValuesWithBetween()
    {
        // 创建一个模拟的 PDOStatement 对象
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->expects($this->exactly(2))
                 ->method('bindValue')
                 ->willReturn(true);
        
        $wheres = [
            ['age', 'BETWEEN', [18, 30]]
        ];
        
        $result = Build::wherePlaceholderValues($mockStmt, $wheres);
        $expected = [
            'where_0_0' => 18,
            'where_0_1' => 30
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 where 方法 - 基本功能
     */
    public function testWhereBasic()
    {
        $wheres = [
            ['id', '=', 1],
            ['name', 'LIKE', '%test%']
        ];
        
        $result = Build::where($wheres);
        $expected = ' WHERE id = :where_0 name LIKE :where_1';
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 where 方法 - 空条件
     */
    public function testWhereEmpty()
    {
        $wheres = [];
        $result = Build::where($wheres);
        $this->assertEquals('', $result);
    }

    /**
     * 测试 fields 方法 - 基本功能
     */
    public function testFieldsBasic()
    {
        $result = Build::fields('id', 'name', 'age');
        $this->assertEquals('id,name,age', $result);
    }

    /**
     * 测试 fields 方法 - 空字段
     */
    public function testFieldsEmpty()
    {
        $result = Build::fields();
        $this->assertEquals('*', $result);
    }

    /**
     * 测试 having 方法 - 基本功能
     */
    public function testHavingBasic()
    {
        $having = [
            ['count(*)', '>', 1]
        ];
        
        $result = Build::having($having);
        $expected = ' HAVING count(*) > :having_0';
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 having 方法 - 空条件
     */
    public function testHavingEmpty()
    {
        $having = [];
        $result = Build::having($having);
        $this->assertEquals('', $result);
    }

    /**
     * 测试 groupBy 方法 - 基本功能
     */
    public function testGroupByBasic()
    {
        $groupby = ['category'];
        $having = [];
        
        $result = Build::groupBy($groupby, $having);
        $this->assertEquals(' GROUP BY category', $result);
    }

    /**
     * 测试 groupBy 方法 - 带有 HAVING 子句
     */
    public function testGroupByWithHaving()
    {
        $groupby = ['category'];
        $having = [
            ['count(*)', '>', 1]
        ];
        
        $result = Build::groupBy($groupby, $having);
        $expected = ' GROUP BY category HAVING count(*) > :having_0';
        $this->assertEquals($expected, $result);
    }

    /**
     * 测试 groupBy 方法 - 空分组
     */
    public function testGroupByEmpty()
    {
        $groupby = [];
        $having = [
            ['count(*)', '>', 1]
        ];
        
        $result = Build::groupBy($groupby, $having);
        $this->assertEquals('', $result);
    }

    /**
     * 测试 orderBy 方法 - 基本功能
     */
    public function testOrderByBasic()
    {
        $orderby = [
            'id' => 'desc',
            'name' => 'asc'
        ];
        
        $result = Build::orderBy($orderby);
        $this->assertEquals(' ORDER BY id desc,name asc', $result);
    }

    /**
     * 测试 orderBy 方法 - 空排序
     */
    public function testOrderByEmpty()
    {
        $orderby = [];
        $result = Build::orderBy($orderby);
        $this->assertEquals('', $result);
    }

    /**
     * 测试 orderBy 方法 - 无效的排序值
     */
    public function testOrderByInvalidValue()
    {
        $orderby = [
            'id' => 'invalid' // 无效的排序值
        ];
        
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('buildOrderBy Order By Key Must Be String,Value Must Be Asc Or Desc. id');
        Build::orderBy($orderby);
    }

    /**
     * 测试 orderBy 方法 - 无效的排序键
     */
    public function testOrderByInvalidKey()
    {
        $orderby = [
            1 => 'desc' // 无效的排序键
        ];
        
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('buildOrderBy Order By Key Must Be String,Value Must Be Asc Or Desc. 1');
        Build::orderBy($orderby);
    }

    /**
     * 测试 limit 方法 - 整数参数
     */
    public function testLimitInteger()
    {
        $result = Build::limit(10);
        $this->assertEquals(' LIMIT 10', $result);
    }

    /**
     * 测试 limit 方法 - 单元素数组
     */
    public function testLimitSingleElementArray()
    {
        $result = Build::limit([10]);
        $this->assertEquals(' LIMIT 10', $result);
    }

    /**
     * 测试 limit 方法 - 双元素数组
     */
    public function testLimitTwoElementArray()
    {
        $result = Build::limit([10, 20]);
        $this->assertEquals(' LIMIT 10 OFFSET 20', $result);
    }

    /**
     * 测试 limit 方法 - 空参数
     */
    public function testLimitEmpty()
    {
        $result = Build::limit([]);
        $this->assertEquals('', $result);
    }

    /**
     * 测试 limit 方法 - 无效的参数
     */
    public function testLimitInvalid()
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('buildLimit Limit Must Be Integer Or Array Be Integer,Not More Than 2');
        Build::limit(['10']); // 字符串数组
    }

    /**
     * 测试 limit 方法 - 超过 2 个元素的数组
     */
    public function testLimitTooManyElements()
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('buildLimit Limit Must Be Integer Or Array Be Integer,Not More Than 2');
        Build::limit([10, 20, 30]); // 超过 2 个元素的数组
    }
}