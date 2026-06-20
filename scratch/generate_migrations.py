import os
import re
from datetime import datetime, timedelta

def snake_to_camel(name):
    return ''.join(word.title() for word in name.split('_'))

def parse_sql(file_path):
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()

    tables = []
    pattern = re.compile(r"CREATE TABLE `([^`]+)`\s*\((.*?)\)\s*ENGINE", re.DOTALL)
    matches = pattern.findall(content)
    
    for match in matches:
        table_name = match[0]
        body = match[1]
        
        columns = []
        primary_keys = []
        indexes = []
        foreign_keys = []
        
        lines = re.split(r',\r?\n', body)
        for line in lines:
            line = line.strip()
            if not line:
                continue
            
            if line.startswith('PRIMARY KEY'):
                pk_match = re.search(r"PRIMARY KEY \(`([^`]+)`\)", line)
                if pk_match:
                    primary_keys.append(pk_match.group(1))
                else:
                    pk_match = re.search(r"PRIMARY KEY \(([^)]+)\)", line)
                    if pk_match:
                        pks = [p.strip().replace('`', '') for p in pk_match.group(1).split(',')]
                        primary_keys.extend(pks)
            elif line.startswith('INDEX') or line.startswith('UNIQUE INDEX'):
                is_unique = 'UNIQUE' in line
                idx_name_match = re.search(r"INDEX `([^`]+)`", line)
                idx_cols_match = re.search(r"\(([^)]+)\)", line)
                if idx_name_match and idx_cols_match:
                    name = idx_name_match.group(1)
                    cols = [c.strip().replace('`', '') for c in idx_cols_match.group(1).split(',')]
                    indexes.append({'name': name, 'columns': cols, 'unique': is_unique})
            elif line.startswith('CONSTRAINT'):
                fk_match = re.search(r"CONSTRAINT `([^`]+)` FOREIGN KEY \(`([^`]+)`\) REFERENCES `([^`]+)` \(`([^`]+)`\)", line)
                if fk_match:
                    foreign_keys.append({
                        'name': fk_match.group(1),
                        'column': fk_match.group(2),
                        'references': fk_match.group(4),
                        'on': fk_match.group(3),
                        'onDelete': 'cascade' if 'ON DELETE CASCADE' in line else ('set null' if 'ON DELETE SET NULL' in line else None),
                        'onUpdate': 'cascade' if 'ON UPDATE CASCADE' in line else ('set null' if 'ON UPDATE SET NULL' in line else None)
                    })
            else:
                # Column parsing
                col_match = re.match(r"`([^`]+)`\s+([a-zA-Z0-9_]+)(?:\(([^)]+)\))?(.*)", line)
                if col_match:
                    col_name = col_match.group(1)
                    col_type = col_match.group(2).lower()
                    col_length = col_match.group(3)
                    col_rest = col_match.group(4)
                    
                    is_unsigned = 'UNSIGNED' in col_rest.upper()
                    is_nullable = 'NOT NULL' not in col_rest.upper()
                    is_auto_increment = 'AUTO_INCREMENT' in col_rest.upper()
                    
                    default_val = None
                    def_match = re.search(r"DEFAULT\s+('[^']*'|[^\s,]+)", col_rest)
                    if def_match:
                        default_val = def_match.group(1)
                        if default_val.upper() == 'NULL':
                            default_val = None
                        elif default_val.startswith("'") and default_val.endswith("'"):
                            pass
                        elif default_val.lower() == 'current_timestamp(0)' or default_val.lower() == 'current_timestamp':
                            default_val = 'CURRENT_TIMESTAMP'
                        elif default_val.lower() == 'curdate':
                            default_val = 'CURRENT_TIMESTAMP'
                    
                    columns.append({
                        'name': col_name,
                        'type': col_type,
                        'length': col_length,
                        'unsigned': is_unsigned,
                        'nullable': is_nullable,
                        'auto_increment': is_auto_increment,
                        'default': default_val,
                        'on_update_current': 'ON UPDATE CURRENT_TIMESTAMP' in col_rest.upper(),
                        'raw': line
                    })

        tables.append({
            'name': table_name,
            'columns': columns,
            'primary_keys': primary_keys,
            'indexes': indexes,
            'foreign_keys': foreign_keys
        })
    return tables

def generate_migrations(tables, output_dir):
    os.makedirs(output_dir, exist_ok=True)
    base_time = datetime.now()
    
    for idx, table in enumerate(tables):
        table_name = table['name']
        
        timestamp_str = (base_time + timedelta(seconds=idx)).strftime('%Y_%m_%d_%H%M%S')
        file_name = f"{timestamp_str}_create_{table_name}_table.php"
        file_path = os.path.join(output_dir, file_name)
        
        php_code = f"<?php\n\nuse Illuminate\\Database\\Migrations\\Migration;\nuse Illuminate\\Database\\Schema\\Blueprint;\nuse Illuminate\\Support\\Facades\\Schema;\n\n"
        php_code += f"return new class extends Migration\n{{\n"
        php_code += f"    public function up()\n    {{\n"
        php_code += f"        Schema::disableForeignKeyConstraints();\n\n"
        php_code += f"        Schema::create('{table_name}', function (Blueprint $table) {{\n"
        
        for col in table['columns']:
            t = col['type']
            l = col['length']
            methods = []
            
            if col['auto_increment'] and t in ['int', 'bigint', 'tinyint']:
                if t == 'bigint':
                    methods.append(f"id('{col['name']}')")
                elif t == 'int':
                    methods.append(f"increments('{col['name']}')")
                elif t == 'tinyint':
                    methods.append(f"tinyIncrements('{col['name']}')")
            else:
                if t == 'bigint':
                    methods.append(f"bigInteger('{col['name']}')")
                elif t == 'int':
                    methods.append(f"integer('{col['name']}')")
                elif t == 'tinyint':
                    methods.append(f"tinyInteger('{col['name']}')")
                elif t == 'varchar':
                    if l: methods.append(f"string('{col['name']}', {l})")
                    else: methods.append(f"string('{col['name']}')")
                elif t == 'char':
                    if l: methods.append(f"char('{col['name']}', {l})")
                    else: methods.append(f"char('{col['name']}')")
                elif t == 'text':
                    methods.append(f"text('{col['name']}')")
                elif t == 'mediumtext':
                    methods.append(f"mediumText('{col['name']}')")
                elif t == 'longtext':
                    methods.append(f"longText('{col['name']}')")
                elif t == 'date':
                    methods.append(f"date('{col['name']}')")
                elif t == 'datetime':
                    methods.append(f"dateTime('{col['name']}')")
                elif t == 'timestamp':
                    methods.append(f"timestamp('{col['name']}')")
                elif t == 'time':
                    methods.append(f"time('{col['name']}')")
                elif t == 'decimal':
                    if l: methods.append(f"decimal('{col['name']}', {l})")
                    else: methods.append(f"decimal('{col['name']}')")
                elif t == 'enum':
                    if l: methods.append(f"enum('{col['name']}', [{l}])")
                    else: methods.append(f"enum('{col['name']}', [])")
                else:
                    methods.append(f"string('{col['name']}')")
                
                if col['unsigned']:
                    methods.append("unsigned()")
                
                if col['nullable']:
                    methods.append("nullable()")
                    
                if col['default'] is not None:
                    if col['default'] == 'CURRENT_TIMESTAMP':
                        methods.append("useCurrent()")
                    else:
                        methods.append(f"default({col['default']})")
                
                if col.get('on_update_current'):
                    methods.append("useCurrentOnUpdate()")
                        
            php_code += "            $table->" + "->".join(methods) + ";\n"
            
        non_auto_inc_pks = [pk for pk in table['primary_keys'] if not any(c['name'] == pk and c['auto_increment'] for c in table['columns'])]
        if non_auto_inc_pks:
            pk_str = ", ".join([f"'{pk}'" for pk in non_auto_inc_pks])
            php_code += f"            $table->primary([{pk_str}]);\n"

        for idx_info in table['indexes']:
            col_str = ", ".join([f"'{c}'" for c in idx_info['columns']])
            idx_name = f"{table_name}_{idx_info['name']}"
            if idx_info['unique']:
                php_code += f"            $table->unique([{col_str}], '{idx_name}');\n"
            else:
                php_code += f"            $table->index([{col_str}], '{idx_name}');\n"
                
        for fk in table['foreign_keys']:
            fk_name = fk['name']
            php_code += f"            $table->foreign('{fk['column']}', '{fk_name}')->references('{fk['references']}')->on('{fk['on']}')"
            if fk['onDelete']:
                php_code += f"->onDelete('{fk['onDelete']}')"
            if fk['onUpdate']:
                php_code += f"->onUpdate('{fk['onUpdate']}')"
            php_code += ";\n"

        php_code += f"        }});\n\n"
        php_code += f"        Schema::enableForeignKeyConstraints();\n"
        php_code += f"    }}\n\n"
        php_code += f"    public function down()\n    {{\n"
        php_code += f"        Schema::disableForeignKeyConstraints();\n"
        php_code += f"        Schema::dropIfExists('{table_name}');\n"
        php_code += f"        Schema::enableForeignKeyConstraints();\n"
        php_code += f"    }}\n}};\n"
        
        with open(file_path, 'w', encoding='utf-8') as f:
            f.write(php_code)
            
    print(f"Successfully generated {len(tables)} migrations.")

if __name__ == '__main__':
    sql_file = r'g:\Project\Distributor\database\mjap.sql'
    out_dir = r'g:\Project\Distributor\database\migrations'
    tables = parse_sql(sql_file)
    generate_migrations(tables, out_dir)
