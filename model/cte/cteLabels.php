<?php
trait globalCte {
    private function cteLabels(): string {
        return "LABELS AS (
                    SELECT
                        id,
                        COALESCE(det.description, def.description, CONCAT(id, ' N/A')) AS name
                    FROM
                        labels
                        LEFT JOIN (
                            SELECT *
                            FROM labeldetails
                            WHERE language_id = '{$this->languageId}'
                        ) det
                            ON labels.id = det.label_id
                        LEFT JOIN (
                            SELECT *
                            FROM labeldetails
                            WHERE language_id = 'en'
                        ) def
                            ON labels.id = def.label_id
                )";
    }

    private function cteQbLabels(&$qb): QueryBuilder {
        return $qb->with('LABELS', function ($q) {
            $q->table('latincon_new.labels')
                ->select(['id', 'COALESCE(det.description, def.description, CONCAT(id, " N/A")) AS name'])
                ->leftJoinSubQuery(
                    function ($q) {
                        $q->table('latincon_new.labeldetails')
                            ->select('*')
                            ->where('language_id', '=', $this->languageId);
                    },
                    'det',
                    'labels.id',
                    '=',
                    'det.label_id'
                )
                ->leftJoinSubQuery(
                    function ($q) {
                        $q->table('latincon_new.labeldetails')
                            ->select('*')
                            ->where('language_id', '=', 'en');
                    },
                    'def',
                    'labels.id',
                    '=',
                    'det.label_id'
                );
        });
    }
}
