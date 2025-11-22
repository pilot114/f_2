## Управление доступом

Управление доступом (как и другой код, связанный с вопросами безопасности), находится
в домене Security.

Управление доступом реализовано на основе стандартного функционала Symfony,
см. https://symfony.com/doc/current/security.html

## Основные понятия

- Пользователь (SecurityUser) - субъект доступа, тот, кому выдаются права на ресурс.
- Ресурс (Resource) - объект доступа.
- Право (Permission) - некоторое действие, разрешенное к выполнению пользователем относительно некоторого ресурса.
- Роль (Role) - сущность, группирующая пользователей и права.

## Типы ресурсов

Список всех ресурсов - **acl.v_all_resources**

Ресурсы делятся на несколько типов, в рамках каждой категории имеют
уникальные поля **id** и **name**

Непосредственно на портале используются `cp_menu` (для доступа к пунктам меню) и cp_action (для доступа к конкретному действию на странице)

| Имя                  | Таблица                                                                                                | Описание                                 | count |
|----------------------|--------------------------------------------------------------------------------------------------------|------------------------------------------|-------|
| cp_menu              | test.cp_departament_part                                                                               | Пункты меню                              | 637   |
| cp_action            | test.acl_resource_item                                                                                 | Действия                                 | 483   |
| rep_report           | reporter.zz_custom_reports                                                                             | Отчеты reporter                          | 1511  |

<details> 
  <summary>
    Есть и другие типы ресурсов, но они используются только на старом бэкенде
  </summary>

  | Имя                  | Таблица                                                                                                | Описание                                 | count |
  |----------------------|--------------------------------------------------------------------------------------------------------|------------------------------------------|-------|
  | acl_role             | acl.roles                                                                                              | Зачем тут роли ???                       | 530   |
  | acl_resource_types   | acl.resource_types                                                                                     | Типы ресурсов (рекурсия)                 | 34    |
  | fin_account          | tehno.finacc                                                                                           | Дерево счетов УУ                         | 707   |
  | fin_employee         | tehno.finemployee                                                                                      | Сотрудники компании                      | 3930  |
  | fin_costscenter      | tehno.finclient e WHERE e.priz = 4                                                                     | Клиенты УУ: ЦФО                          | 636   |
  | fin_enterprise       | tehno.finclient e WHERE e.priz in (2,41)                                                               | Клиенты УУ: Предприятие + Высший уровень | 90    |
  | fin_operation        | tehno.finoper WHERE o.closed_from is null or o.closed_from < sysdate                                   | Операции УУ                              | 341   |
  | fin_menu             | tehno.fin_objects o WHERE o.visible = 1 and o.obj_id is not null                                       | Меню, показываемое в клиенте УУ          | 85    |
  | fin_action           | tehno.finoperation                                                                                     | Справочник интерфейсных операций         | 132   |
  | dir_category         | tehno.type_tovara                                                                                      | Типы товара                              | 16    |
  | dir_country          | tehno.country UNION -1 (все страны)                                                                    | Страны                                   | 232   |
  | dir_fields           | hardcode                                                                                               | Параметры продукта                       | 10    |
  | dir_country_field    | dir_country(см.выше) CROSS JOIN dir_fields (см.выше)                                                   | Параметры продукта в стране              | 2320  |
  | cp_overdraft_country | tehno.country UNION -1 (все страны)                                                                    | Страны                                   | 232   |
  | net_country          | net.country                                                                                            | Страны                                   | 232   |
  | country_region       | net.country_region                                                                                     | Регионы + Страны                         | 241   |
  | auth_country         | net.country UNION 1000 (все страны) UNION 1001 ('Под 99999') CROSS JOIN (1 = ЦОК' и 2 = 'Консультант') | Страны с типом доступа (?)               | 467   |
  | net$country_rests    | net.country UNION -1 (все страны)                                                                      | Страны                                   | 233   |
  | co_country           | sibvaleo.site_ruscity_country WHERE tehno.sklads.country_id = sibvaleo.site_ruscity_country            | ?                                        | 49    |
  | mats_cs              | budget.mv_costscenters_light                                                                           | ЦФО в заявках ТМЦ                        | 628   |
  | ui$menu              | gui.ui$menu_item WHERE item_type = 'block'                                                             | Пункты меню                              | 375   |
  | ui$action            | gui.ui$block_action                                                                                    | Действия в блоке                         | 1138  |
  | co_currency          | tehno.currency                                                                                         | Валюты в заявках                         | 45    |
  | uk_action            | tehno.uk_action                                                                                        | Действия в программе УК                  | 5     |
  | ishop_adm            | test.nc_product_right                                                                                  | Права для админки ИМ                     | 6     |
  | task$select_depart   | test.cp_departament d WHERE d.id in (244,1982)                                                         | Департаменты (всего 2?) *                | 2     |
  | astra_module         | tehno.sw_module                                                                                        |                                          | 92    |
  | astra_action         | tehno.sw_action                                                                                        |                                          | 1     |
  | equip_nodes          | tehno.sw_equip_nodes                                                                                   |                                          | 1     |

Примечания:
- **task$select_depart** - Выбор департамента как исполнителя в задаче
- есть в **acl.resource_types**, но не используется в view:
  - **mk_action** (Действия МК)
  - **po_depart** (Заявки на платёж, доступ к заявкам подразделения)

</details>

## Права

Список всех прав - **acl.v_access**

Право содержит:
- вариант привязки: по пользователю, по штатной единице, по роли (ссылка на **cp_emp** ИЛИ на **acl.roles**)
- тип доступа (**read / write / execute**), могут быть кастомные варианты
- признак делегированности
- имеют уникальные поля **id** и **name**

## Аттрибут CpAction

Примеры:

    // простая проверка
    #[CpAction('awards_directory.edit')]

    // комбинация проверок с помощью symfony expression language.
    // A,B,C,D - коды ресурса, например `awards_directory.edit`
    #[CpAction('A || (B && C) || !D')]

## Аттрибут CpMenu

Примеры:

    // простая проверка
    #[CpMenu('callcenter/call-center-bot')]
