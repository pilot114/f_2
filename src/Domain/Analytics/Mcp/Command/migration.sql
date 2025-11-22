CREATE TABLE test.cp_artefact (
    id NUMBER PRIMARY KEY,
    name VARCHAR2(255) NOT NULL,
    type VARCHAR2(50) NOT NULL,
    content CLOB NOT NULL
);

CREATE SEQUENCE test.cp_artefact_sq
    START WITH 1
    INCREMENT BY 1
    NOCACHE
    NOCYCLE;

CREATE OR REPLACE TRIGGER test.cp_artefact_bi_trg
    BEFORE INSERT ON test.cp_artefact
    FOR EACH ROW
BEGIN
    IF :new.id IS NULL THEN
        SELECT test.cp_artefact_sq.NEXTVAL
        INTO :new.id
        FROM dual;
    END IF;
END;

CREATE UNIQUE INDEX idx_cp_artefact_type_name_uniq ON test.cp_artefact (type, name);

----------------------------------------------------------

CREATE SEQUENCE test.cp_artefact_link_sq
    START WITH 1
    INCREMENT BY 1
    NOCACHE
    NOCYCLE;

CREATE TABLE test.cp_artefact_link (
   id NUMBER PRIMARY KEY,
   from_id NUMBER NOT NULL,
   to_id NUMBER NOT NULL,
   type VARCHAR2(50),

   CONSTRAINT fk_cp_artefact_link_from FOREIGN KEY (from_id)
       REFERENCES test.cp_artefact(id),
   CONSTRAINT fk_cp_artefact_link_to FOREIGN KEY (to_id)
       REFERENCES test.cp_artefact(id),
   CONSTRAINT uk_cp_artefact_link_from_to_type UNIQUE (from_id, to_id, type)
);

CREATE OR REPLACE TRIGGER test.cp_artefact_link_bi_trg
    BEFORE INSERT ON test.cp_artefact_link
    FOR EACH ROW
BEGIN
    IF :new.id IS NULL THEN
        SELECT test.cp_artefact_link_sq.NEXTVAL
        INTO :new.id
        FROM dual;
    END IF;
END;
