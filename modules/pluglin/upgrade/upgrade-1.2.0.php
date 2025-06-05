<?php

function upgrade_module_1_2_0()
{
    return Db::getInstance()
            ->execute(
                'ALTER TABLE `'._DB_PREFIX_.'pluglin_content` 
                CHANGE `read` `read` int(11) NOT NULL DEFAULT 0,
                CHANGE `send` `send` int(11) NOT NULL DEFAULT 0'
            );
}
