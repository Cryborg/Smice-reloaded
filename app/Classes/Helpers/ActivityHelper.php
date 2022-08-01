<?php

namespace App\Classes\Helpers;


class ActivityHelper
{
    /**
     * @param integer $societyId
     * @param array $filter
     * @return integer
     */
    public static function getUsersActivityBySociety($societyId, $filter)
    {
        $wave_id = implode(",", ArrayHelper::getIds($filter['waves']));
        $shop_id = implode(",", ArrayHelper::getIds($filter['shops']));

        $groups = ArrayHelper::getIds($filter['usersGroups']);

        $groupsSQL = count($groups) ? ' AND user_group IN (' . implode(",", $groups) . ')' : '';

        $sql = 'SELECT count("times") AS users_done_missions
                FROM (
                    SELECT count(id) AS times
                    FROM show_wave_users
                    WHERE refused = FALSE
                    AND retried = FALSE
                    AND status = \'read\'
                    AND wave_id IN (' . $wave_id . ')
                    AND shop_id IN (' . $shop_id . ')
                    AND user_society = ?
                    ' . $groupsSQL . '
                    GROUP BY user_id
                    ) AS users
                WHERE times > 0';
        $result = \DB::select($sql, [$societyId]);

        return $result[0]['users_done_missions'];
    }

    public static function getUsersBySociety($societyId, $filter)
    {
        $groups = ArrayHelper::getIds($filter['usersGroups']);
        $shop_id = implode(",", ArrayHelper::getIds($filter['shops']));
        $groupsSQL = count($groups) ? ' AND group_user.group_id IN (' . implode(",", $groups) . ')' : '';
        $inner = count($groups) ? ' inner JOIN "public".group_user ON "public".group_user.user_id = "public".user.id' : '';
        $inner2 = count($shop_id) ? ' inner JOIN "public".user_shop ON "public".user_shop.user_id = "public".user.id' : '';
        $groupsSQL2 = count($shop_id) ? ' AND user_shop.shop_id IN (' . $shop_id . ')' : '';
        $sql = 'SELECT count(DISTINCT "public".user.id) AS nb_users
                    FROM "public".user
                    ' . $inner . $inner2 . ' 
                    WHERE "public".user.society_id = ? ' . $groupsSQL . $groupsSQL2;
        $result = \DB::select($sql, [$societyId]);
        return $result[0]['nb_users'];
    }

    /**
     * @param int $societyId
     * @param array $filter
     * @return int
     */
    public static function getUsersWithMissionCount($societyId, $filter)
    {
        $wave_id = implode(",", ArrayHelper::getIds($filter['waves']));
        $shop_id = implode(",", ArrayHelper::getIds($filter['shops']));
        $groups = ArrayHelper::getIds($filter['usersGroups']);

        $groupsSQL = count($groups) ? ' AND user_group IN (' . implode(",", $groups) . ')' : '';

        $sql = 'SELECT count("times") AS users_done_missions
                FROM (
                    SELECT count(id) AS times
                    FROM show_wave_users
                    WHERE refused = FALSE
                    AND retried = FALSE
                    AND wave_id IN (' . $wave_id . ')
                    AND shop_id IN (' . $shop_id . ')
                    AND user_society = ?
                    ' . $groupsSQL . '
                    GROUP BY user_id
                    ) AS users';
        $result = \DB::select($sql, [$societyId]);

        return $result[0]['users_done_missions'];
    }

    /**
     * @param int $societyId
     * @param array $filter
     * @return array
     */
    public static function getUsersActivityByMissionsCountOnSociety($societyId, $filter)
    {
        $wave_id = implode(",", ArrayHelper::getIds($filter['waves']));
        $shop_id = implode(",", ArrayHelper::getIds($filter['shops']));
        $groups = ArrayHelper::getIds($filter['usersGroups']);
        $groupsSql = (count($groups)) ? ' AND user_group IN (' . implode(",", $groups) . ')' : '';

        $sql = 'SELECT times AS missions_done, count("user") AS users_count
                FROM (
                    SELECT count(id) AS times, "user"
                    FROM show_wave_users
                    WHERE status = \'read\'
                    AND wave_id IN (' . $wave_id . ')
                    AND shop_id IN (' . $shop_id . ')
                    AND user_society = ?
                    ' . $groupsSql . '
                    GROUP BY "user"
                    ) AS users_missions_done
                GROUP BY times
                ORDER BY times DESC';

        $result = \DB::select($sql, [$societyId]);

        return $result;
    }

    /**
     * @param int $societyId
     * @param array $targetIds
     * @return int
     */
    public static function getShopsCountOnSociety($societyId, $targetIds)
    {
        $result = \DB::table('show_shops_from_wave_target')->select('id')->where('society_id', $societyId)
            ->whereIn('wave_target_id', $targetIds)->groupBy('id')->get();

        return (count($result) > 0) ? count($result) : 0;
    }

    /**
     * @param int $societyId
     * @param array $filter
     * @return int
     */
    public static function getShopsOnSociety($societyId, $filter)
    {
        $axes = ArrayHelper::getIds($filter['axes']);
        $shop_id = implode(",", ArrayHelper::getIds($filter['shops']));
        $groupsSQL = count($axes) ? ' AND shop_axe.axe_id IN (' . implode(",", $axes) . ')' : '';
        $inner = count($axes) ? ' INNER JOIN "public".shop_axe ON "public".shop_axe.shop_id = "public".shop.id' : '';

        $sql = 'SELECT count("public".shop.id) AS nb_shops
                    FROM "public".shop
                    ' . $inner . '
                    INNER JOIN "public".shop_society ON "public".shop_society.shop_id = "public".shop.id
                    WHERE shop.id IN (' . $shop_id . ')
                    AND "public".shop_society.society_id = ? ' . $groupsSQL;
        $result = \DB::select($sql, [$societyId]);

        return $result[0]['nb_shops'];
    }

    /**
     * @param int $societyId
     * @param array $filter
     * @return int
     */
    public static function getShopsCountByMissionsOnSociety($societyId, $filter)
    {
        $wave_id = implode(",", ArrayHelper::getIds($filter['waves']));
        $shop_id = implode(",", ArrayHelper::getIds($filter['shops']));
        $axes = ArrayHelper::getIds($filter['axes']);
        $groupsSQL = count($axes) ? ' AND shop_axe.axe_id IN (' . implode(",", $axes) . ')' : '';
        $sql = 'SELECT "public".shop.id
                    FROM "public".shop
                    INNER JOIN "public".shop_axe ON "public".shop_axe.shop_id = "public".shop.id
                    INNER JOIN "public".shop_society ON "public".shop_society.shop_id = "public".shop.id
                    WHERE shop.id IN (' . $shop_id . ')
                    AND "public".shop_society.society_id = ? ' . $groupsSQL;
        $result = \DB::select($sql, [$societyId]);

        //Get all shop_id from axe
        $shop_id = ArrayHelper::getIds($result);
        $shop_id = implode(",", $shop_id);
        $result = \DB::select('SELECT count("name") AS shops_missions_count
                                FROM (
                                    SELECT count(wave_id) waves, "name" 
                                    FROM show_shops_from_wave_target
                                    WHERE society_id = ?
                                    AND wave_id IN (' . $wave_id . ')                                    
                                    AND id IN (' . $shop_id . ')                                    
                                    GROUP BY "name"
                                ) AS a', [$societyId]);

        return $result[0]['shops_missions_count'];
    }

    /**
     * @param int $societyId
     * @param array $filter
     * @return array
     */
    public static function getUsersMissionsTable($societyId, $filter)
    {
        $wave_id = ArrayHelper::getIds($filter['waves']);
        $shop_id = ArrayHelper::getIds($filter['shops']);
        $user_list = [];
        $result = \DB::table('show_wave_users')->select(['user_id', 'first_name', 'last_name', 'email'])
            ->selectRaw('count(id) as times')
            ->where(['user_society' => $societyId, 'refused' => false, 'retried' => false])
            ->where('status', 'read')
            ->whereIn('wave_id', $wave_id)
            ->whereIn('shop_id', $shop_id);
        if (count($filter['usersGroups'])) {
            $result = $result->whereIn('user_group', ArrayHelper::getIds($filter['usersGroups']));
        }
        $result = $result->groupBy(['user_id', 'first_name', 'last_name', 'email'])->orderBy('times', 'desc')->get();

        foreach ($result as $key => $value) {
            array_push($user_list, $value['user_id']);
        }

        $groups = ArrayHelper::getIds($filter['usersGroups']);
        $shop_id = implode(",", $shop_id);
        $user_list = implode(",", $user_list);
        $groupsSQL = count($groups) ? ' AND group_user.group_id IN (' . implode(",", $groups) . ')' : '';
        $inner = count($groups) ? ' inner JOIN "public".group_user ON "public".group_user.user_id = "public".user.id' : '';
        $inner2 = count($shop_id) ? ' inner JOIN "public".user_shop ON "public".user_shop.user_id = "public".user.id' : '';
        $groupsSQL2 = count($shop_id) ? ' AND user_shop.shop_id IN (' . $shop_id . ')' : '';
        $sql = 'SELECT first_name, last_name, email 
                    FROM "public".user
                    ' . $inner . $inner2 . ' 
                    WHERE "public".user.society_id = ? ' . $groupsSQL . $groupsSQL2 . ' AND "public".user.id not in (' . $user_list . ') GROUP BY (first_name, last_name, email)';
        $all_user = \DB::select($sql, [$societyId]);

        $result = array_merge($result, $all_user);
        return $result;

    }

    /**
     * @param int $societyId
     * @param array $targetIds
     * @param array $filter
     * @return array
     */
    public static function getShopsMissionsCount($societyId, $targetIds, $filter)
    {
        $wave_id = implode(",", ArrayHelper::getIds($filter['waves']));
        $shop_id = implode(",", ArrayHelper::getIds($filter['shops']));
        $targetIds = implode(",", $targetIds);

        $result = \DB::select('SELECT waves AS missions_done, count("name") AS shops
                                FROM (
                                    SELECT count(wave_id) waves, "name" 
                                    FROM show_shops_from_wave_target
                                    WHERE society_id = ?
                                    AND wave_id IN (' . $wave_id . ')                                   
                                    AND id IN (' . $shop_id . ')                                   
                                    AND wave_target_id IN (' . $targetIds . ')
                                    GROUP BY "name"
                                ) a
                                GROUP BY waves
                        ORDER BY waves DESC', [$societyId]);
        return $result;
    }

    /**
     * @param int $societyId
     * @param array $filter
     * @return array
     */
    public static function getShopsMissionsTable($societyId, $filter)
    {
        $wave_id = ArrayHelper::getIds($filter['waves']);
        $shop_id = implode(",", ArrayHelper::getIds($filter['shops']));
        $shop_list = [];
        $axes = ArrayHelper::getIds($filter['axes']);
        $groupsSQL = count($axes) ? ' AND shop_axe.axe_id IN (' . implode(",", $axes) . ')' : '';
        $sql = 'SELECT "public".shop.id
                    FROM "public".shop
                    INNER JOIN "public".shop_axe ON "public".shop_axe.shop_id = "public".shop.id
                    INNER JOIN "public".shop_society ON "public".shop_society.shop_id = "public".shop.id
                    WHERE id IN (' . $shop_id . ')        
                    AND "public".shop_society.society_id = ? ' . $groupsSQL;
        $result = \DB::select($sql, [$societyId]);
        //Get all shop_id from axe
        $shop_id = ArrayHelper::getIds($result);

        $result = \DB::table('show_shops_from_wave_target')->select(['id', 'name', 'city', 'country'])
            ->selectRaw('count(wave_id) missions')->where('society_id', $societyId)
            ->whereIn('wave_id', $wave_id)->whereIn('id', $shop_id)->groupBy(['id', 'name', 'city', 'country'])->get();

        foreach ($result as $key => $value) {
            array_push($shop_list, $value['id']);
        }
        $shop_list = implode(",", $shop_list);
        $shop_id = implode(",", ArrayHelper::getIds($filter['shops']));
        $inner = count($axes) ? ' INNER JOIN "public".shop_axe ON "public".shop_axe.shop_id = "public".shop.id' : '';

        $sql = 'SELECT id, name, city, country
                    FROM "public".shop
                    ' . $inner . '
                    INNER JOIN "public".shop_society ON "public".shop_society.shop_id = "public".shop.id
                    WHERE shop.id IN (' . $shop_id . ') AND shop.id NOT IN (' . $shop_list . ') 
                    AND "public".shop_society.society_id = ? ' . $groupsSQL;
        $result2 = \DB::select($sql, [$societyId]);
        $result = array_merge($result, $result2);

        return $result;
    }

    /**
     * @param integer $societyId
     * @param array $filter
     * @return array
     */
    public static function getUsersAchieved($societyId, $filter)
    {
        $wave_id = implode(",", ArrayHelper::getIds($filter['waves']));
        $shop_id = implode(",", ArrayHelper::getIds($filter['shops']));
        $groups = ArrayHelper::getIds($filter['usersGroups']);
        $groupsSql = (count($groups)) ? ' AND group_user.group_id IN (' . implode(",", $groups) . ')' : '';

         $sql = 'SELECT count("name") AS us, "name" from (
         SELECT count(wt.user_id) us, w."id", w."name"
                FROM wave w
                JOIN wave_target wt
                ON wt.wave_id = w.id
                LEFT JOIN group_user ON (group_user.user_id = wt.user_id)
                WHERE w.id IN (' . $wave_id . ')
                AND w.society_id = ?
                ' . $groupsSql . '
                AND status = \'read\'
                AND shop_id IN (' . $shop_id . ')    
                GROUP BY wt.user_id, w."name", w."id") a
                GROUP BY name, id
                ORDER BY id';

        $result = \DB::select($sql, [$societyId]);
        return $result;
    }

    /**
     * @param int $societyId
     * @param array $filter
     * @return array
     */
    public static function getShopsAchieved($societyId, $filter)
    {
        $waves = implode(",", ArrayHelper::getIds($filter['waves']));
        $shop_id = implode(",", ArrayHelper::getIds($filter['shops']));
        $axes = ArrayHelper::getIds($filter['axes']);
        $groupsSql = count($axes) ? ' AND shop_axe.axe_id IN (' . implode(",", $axes) . ')' : '';

        $sql = 'SELECT count("name") AS shops, "name" from (
                SELECT count(wt.shop_id) shops, w."id", w."name"
                FROM wave w
                JOIN wave_target wt
                ON wt.wave_id = w.id
                INNER JOIN shop_axe ON shop_axe.shop_id = wt.shop_id
                WHERE w.id IN (' . $waves . ')
                AND w.society_id = ?
                 ' . $groupsSql . '
                AND status = \'read\'
                AND wt.shop_id IN (' . $shop_id . ')        
                GROUP BY wt.shop_id, w."name", w."id") a
                GROUP BY name, id
                ORDER BY id  ;';
        $result = \DB::select($sql, [$societyId]);

        return $result;
    }
}
