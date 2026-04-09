<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Model;
use system\model\ModelException;
use system\database\DatabaseAbstract;

class ModelTransactionTest extends TestCase
{
    /**
     * 测试 transaction 方法 - 回调函数执行成功
     */
    public function testTransactionCallbackSuccess()
    {
        // 创建数据库模拟对象
        $dbMock = $this->createMock(DatabaseAbstract::class);
        
        // 模拟数据库的 transaction 方法，返回成功
        $dbMock->expects($this->once())
            ->method('transaction')
            ->willReturn(true);
        
        // 模拟数据库的 commit 方法，返回成功
        $dbMock->expects($this->once())
            ->method('commit')
            ->willReturn(true);
        
        // 模拟数据库的 rollback 方法，应该被调用
        $dbMock->expects($this->never())
            ->method('rollback')
            ->willReturn(true);

        // 创建一个继承自 Model 的测试类
        $model = new class($dbMock) extends Model {
            public function __construct($db)
            {
                parent::__construct($db);
                $this->table = 'test_table';
                $this->primary = 'id';
            }
        };

        // 测试回调函数执行成功的情况
        $result = $model->transaction(function () {
            return 'Success';
        });
        $this->assertEquals('Success', $result);
    }

    /**
     * 测试 transaction 方法 - 回调函数抛出异常
     */
    public function testTransactionCallbackThrowsException()
    {
        // 创建数据库模拟对象
        $dbMock = $this->createMock(DatabaseAbstract::class);
        
        // 模拟数据库的 transaction 方法，返回成功
        $dbMock->expects($this->once())
            ->method('transaction')
            ->willReturn(true);
        
        // 模拟数据库的 commit 方法，不应该被调用
        $dbMock->expects($this->never())
            ->method('commit');
        
        // 模拟数据库的 rollback 方法，应该被调用
        $dbMock->expects($this->once())
            ->method('rollback')
            ->willReturn(true);

        // 创建一个继承自 Model 的测试类
        $model = new class($dbMock) extends Model {
            public function __construct($db)
            {
                parent::__construct($db);
                $this->table = 'test_table';
                $this->primary = 'id';
            }
        };

        // 测试回调函数抛出异常的情况
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Transaction Error: Test exception');
        $model->transaction(function () {
            throw new \Exception('Test exception');
        });
    }

    /**
     * 测试 transaction 方法 - 事务开始失败
     */
    public function testTransactionStartFailure()
    {
        // 创建数据库模拟对象
        $dbMock = $this->createMock(DatabaseAbstract::class);
        
        // 模拟数据库的 transaction 方法，抛出异常
        $dbMock->expects($this->once())
            ->method('transaction')
            ->willThrowException(new \Exception('Transaction start failed'));
        
        // 模拟数据库的 commit 方法，不应该被调用
        $dbMock->expects($this->never())
            ->method('commit');
        
        // 模拟数据库的 rollback 方法，应该被调用
        $dbMock->expects($this->once())
            ->method('rollback')
            ->willReturn(true);

        // 创建一个继承自 Model 的测试类
        $model = new class($dbMock) extends Model {
            public function __construct($db)
            {
                parent::__construct($db);
                $this->table = 'test_table';
                $this->primary = 'id';
            }
        };

        // 测试事务开始失败的情况
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Transaction start failed');
        $model->transaction(function () {
            return 'Success';
        });
    }

    /**
     * 测试 transaction 方法 - 事务提交失败
     */
    public function testTransactionCommitFailure()
    {
        // 创建数据库模拟对象
        $dbMock = $this->createMock(DatabaseAbstract::class);
        
        // 模拟数据库的 transaction 方法，返回成功
        $dbMock->expects($this->once())
            ->method('transaction')
            ->willReturn(true);
        
        // 模拟数据库的 commit 方法，抛出异常
        $dbMock->expects($this->once())
            ->method('commit')
            ->willThrowException(new \Exception('Transaction commit failed'));
        
        // 模拟数据库的 rollback 方法，应该被调用
        $dbMock->expects($this->once())
            ->method('rollback')
            ->willReturn(true);

        // 创建一个继承自 Model 的测试类
        $model = new class($dbMock) extends Model {
            public function __construct($db)
            {
                parent::__construct($db);
                $this->table = 'test_table';
                $this->primary = 'id';
            }
        };

        // 测试事务提交失败的情况
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Transaction commit failed');
        $model->transaction(function () {
            return 'Success';
        });
    }

    /**
     * 测试 transaction 方法 - 事务回滚失败
     */
    public function testTransactionRollbackFailure()
    {
        // 创建数据库模拟对象
        $dbMock = $this->createMock(DatabaseAbstract::class);
        
        // 模拟数据库的 transaction 方法，返回成功
        $dbMock->expects($this->once())
            ->method('transaction')
            ->willReturn(true);
        
        // 模拟数据库的 commit 方法，不应该被调用
        $dbMock->expects($this->never())
            ->method('commit');
        
        // 模拟数据库的 rollback 方法，抛出异常
        $dbMock->expects($this->once())
            ->method('rollback')
            ->willThrowException(new \Exception('Transaction rollback failed'));

        // 创建一个继承自 Model 的测试类
        $model = new class($dbMock) extends Model {
            public function __construct($db)
            {
                parent::__construct($db);
                $this->table = 'test_table';
                $this->primary = 'id';
            }
        };

        // 测试事务回滚失败的情况
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Transaction rollback failed');
        $model->transaction(function () {
            throw new \Exception('Test exception');
        });
    }

    /**
     * 测试 transaction 方法 - 回调函数返回不同类型的值
     */
    public function testTransactionCallbackReturnsDifferentTypes()
    {
        // 创建数据库模拟对象
        $dbMock = $this->createMock(DatabaseAbstract::class);
        
        // 模拟数据库的 transaction 方法，返回成功
        $dbMock->expects($this->exactly(5))
            ->method('transaction')
            ->willReturn(true);
        
        // 模拟数据库的 commit 方法，返回成功
        $dbMock->expects($this->exactly(5))
            ->method('commit')
            ->willReturn(true);
        
        // 模拟数据库的 rollback 方法，不应该被调用
        $dbMock->expects($this->never())
            ->method('rollback');

        // 创建一个继承自 Model 的测试类
        $model = new class($dbMock) extends Model {
            public function __construct($db)
            {
                parent::__construct($db);
                $this->table = 'test_table';
                $this->primary = 'id';
            }
        };

        // 测试回调函数返回字符串
        $result1 = $model->transaction(function () {
            return 'String value';
        });
        $this->assertEquals('String value', $result1);

        // 测试回调函数返回整数
        $result2 = $model->transaction(function () {
            return 123;
        });
        $this->assertEquals(123, $result2);

        // 测试回调函数返回浮点数
        $result3 = $model->transaction(function () {
            return 123.45;
        });
        $this->assertEquals(123.45, $result3);

        // 测试回调函数返回数组
        $result4 = $model->transaction(function () {
            return ['key' => 'value'];
        });
        $this->assertEquals(['key' => 'value'], $result4);

        // 测试回调函数返回 null
        $result5 = $model->transaction(function () {
            return null;
        });
        $this->assertNull($result5);
    }
}