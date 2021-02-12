<?php
return [
    "teams"=> [
        "relations"=> [
            "team_count"=> [
                "table"=> "team_count",
                "field"=> "team",
                "type"=> "inbound"
            ]
        ]
    ],
    "team_count"=> [
        "fields"=> [
            "team"=> [
                "iskey"=> true
            ]
        ],
        "keyFld"=> "team"
    ]
];