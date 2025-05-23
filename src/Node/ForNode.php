<?php

namespace Compiler\Node;

class ForNode extends StatementNode
{
    public ?StatementNode  $init;
    public ?ExpressionNode $condition;
    public ?StatementNode  $post;
    public StatementNode   $body;

    public function __construct(
        ?StatementNode  $init,
        ?ExpressionNode $condition,
        ?StatementNode  $post,
        StatementNode   $body
    ) {
        $this->init      = $init;
        $this->condition = $condition;
        $this->post      = $post;
        $this->body      = $body;
    }
}
