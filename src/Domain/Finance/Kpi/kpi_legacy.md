
### Старые сервисы, где упоминается KPI

- выставление в сессию права на просмотр KPI

Можно просматривать и назначать KPI, выставлять задания и коэффиценты,
а также отправить данные на почту по KPI сотрудника
https://cp.siberianhealth.com/company/kpi/
https://cp.siberianhealth.com/company/kpi/edit.php

- выставление в сессию права на просмотр KPI (2013)

        $sql = 'select count(*) cnt from kpi_task where idemp = :idemp';
        $res = sql_execute($sql, $POST);
        if ($res[0]['cnt'] > 0) {
        $arr['kpi']['allow']['view'] = 'view';
        }
        class company_kpi {
        //    RW на 2 таблицы
        //    kpi_emp_opt
        //    from kpi_task
        }

- Процедуры в логистике по добавлению коммента к KPI (из списка) 2018 г.

https://cp.siberianhealth.com/reports/logistic

Админ сервиса - Бакалкина Дарья **bakalkina.ds@sibvaleo.com** (может привязать страну).
Комментарий доступен для редактирования до 10-го числа следующего месяца
Есть выгрузка в Excel + **CRUD на dwh.bg_def_prod_comment**
Редактирование существующих комментариев для продуктов [KPI дефицит ГСК] или [KPI Запас]

КПИ дефицит ГСК **analysis.pkg_cp.get_lgst_kpi_def_rep**  
КПИ Запас **analysis.pkg_cp.get_lgst_kpi_provide_rep**

- Нерабочий "прототип"

https://cp.siberianhealth.com/feo/kpi

- Нерабочий KPI по ЦОК

https://cp.siberianhealth.com/reports/kpi_cok
Через **analysis.pkg_cp.get_lgst_kpi_comment_ref** получаем комментарий к KPI

- возможность загрузить и выгрузить KPI файлом

https://cp.siberianhealth.com/personal/emp.php# (Показатели) 2019
SELECT * FROM cp_files where parent_tbl = 'user_kpi' (ровно 1 файл)

- отчет по задачам для KPI (от KPI только название)

- https://cp.siberianhealth.com/company/report/kpi_department.php