<?php

namespace Compiler\Node;

class FunctionNode extends StatementNode
{
    public string           $returnType;
    public string           $name;
    public array            $parameters;
    public BlockNode        $body;

    public function __construct(
        string    $returnType,
        string    $name,
        array     $parameters,
        BlockNode $body
    ) {
        $this->returnType = $returnType;
        $this->name       = $name;
        $this->parameters = $parameters;
        $this->body       = $body;
    }
}
