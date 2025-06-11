import csv
import psycopg2
from psycopg2.extras import execute_batch

DB_HOST = 'srv-prj-new.iut-acy.local'
DB_PORT = 5432
DB_NAME = 'rta2'
DB_USER = 'rta2'
DB_PASS = 'Maurice'

CSV_FILE = r'C:\xampp\htdocs\SAE203-suivi-de-colis-1\CLIENT-Oscar\DATA\LOCAL\postaldata.csv'

# Map CSV columns to DB columns
CSV_TO_DB = {
    'identifiant_a': 'acores_id',
    'libelle_du_site': 'name',
    'caracteristique_du_site': 'site_type',
    'site_acores_de_rattachement': 'parent_office_acores_id',
    'adresse': 'street_address',
    'complement_d_adresse': 'address_complement',
    'code_postal': 'postal_code',
    'localite': 'city',
    'code_insee': 'insee_code',
    'pays': 'country',
    'latitude': 'latitude',
    'longitude': 'longitude',
    'numero_de_telephone': 'phone_number'
}

def main():
    print("Connecting to the database...")
    conn = psycopg2.connect(
        host=DB_HOST,
        port=DB_PORT,
        dbname=DB_NAME,
        user=DB_USER,
        password=DB_PASS
    )
    print("Connection established.")
    cur = conn.cursor()

    print(f"Opening CSV file: {CSV_FILE}")
    with open(CSV_FILE, newline='', encoding='utf-8') as csvfile:
        reader = list(csv.DictReader(csvfile))
        print(f"Preparing {len(reader)} rows for insertion...")
        db_rows = []
        phone_base = 600000000  # Start from 0600000000 (French mobile format)
        printed = 0
        for idx, row in enumerate(reader):
            db_row = {db_col: row[csv_col] for csv_col, db_col in CSV_TO_DB.items()}
            # Clean and set parent_office_acores_id properly
            parent_id = db_row.get('parent_office_acores_id', '')
            parent_id_clean = str(parent_id).strip()
            if parent_id_clean == '' or parent_id_clean.upper() == 'NULL':
                db_row['parent_office_acores_id'] = None
            else:
                db_row['parent_office_acores_id'] = parent_id_clean
                if printed < 10:
                    print(f"Row {idx} parent_office_acores_id: {repr(db_row['parent_office_acores_id'])}")
                    printed += 1
            # Override phone_number with unique French number
            db_row['phone_number'] = f"0{phone_base + idx:09d}"
            db_rows.append(db_row)
        columns = ', '.join(db_rows[0].keys())
        placeholders = ', '.join(['%s'] * len(db_rows[0]))
        values_list = [list(db_row.values()) for db_row in db_rows]
        sql = f"INSERT INTO post_offices ({columns}) VALUES ({placeholders})"
        # DEBUG: Insert first 100 rows one by one to catch errors
        print("\nDEBUG: Inserting first 100 rows one by one to check for errors...")
        for idx, db_row in enumerate(db_rows[:100]):
            try:
                cur.execute(
                    f"INSERT INTO post_offices ({columns}) VALUES ({placeholders})",
                    list(db_row.values())
                )
            except Exception as e:
                print(f"Error inserting row {idx} (parent_office_acores_id={db_row['parent_office_acores_id']}): {e}")
                conn.rollback()
        print("DEBUG: Done with first 100 rows.\n")
        print(f"Inserting {len(values_list)} rows in batches...")
        try:
            execute_batch(cur, sql, values_list, page_size=1000)
        except Exception as e:
            print(f"Error during batch insert: {e}")
            conn.rollback()
            cur.close()
            conn.close()
            return

    print("Committing changes to the database...")
    conn.commit()
    cur.close()
    conn.close()
    print("CSV data uploaded successfully.")

if __name__ == '__main__':
    main()