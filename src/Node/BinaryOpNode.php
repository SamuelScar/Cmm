<?php
namespace Compiler\Node;

class BinaryOpNode extends ExpressionNode
{
    public string $op;
    public ExpressionNode $left;
    public ExpressionNode $right;

    public function __construct(string $op, ExpressionNode $left, ExpressionNode $right)
    {
        $this->op    = $op;
        $this->left  = $left;
        $this->right = $right;
    }
}
