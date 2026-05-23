import sys
import json
import pandas as pd
from sklearn.cluster import KMeans

import os
os.environ['OPENBLAS_NUM_THREADS'] = '1'
os.environ['MKL_NUM_THREADS'] = '1'

def solve():
    try:
        input_data = sys.stdin.read()
        if not input_data:
            print(json.dumps({"error": "No se recibieron datos"}))
            return

        data = json.loads(input_data)
        df = pd.DataFrame(data)

        mapeo = {
            'Activo': 1, 'En servicio': 1, 'Pertenece al ejido': 1,
            'Difunto': 0, 'Baja': 0, 'Proceso de sucesión': 0,
            'Enfermo': 2, 'Suspendido': 2, 'Suspensión': 2
        }
        
        # Creamos la columna numérica basada en el estatus
        df['valor_ia'] = df['estatus'].map(mapeo).fillna(0)

        #Ejecutar K-Means (Solo con los hilos necesarios para evitar errores de socket)
        #Usamos n_init='auto' para compatibilidad
        kmeans = KMeans(n_clusters=3, random_state=42, n_init=10)
        df['cluster'] = kmeans.fit_predict(df[['valor_ia']])

        #Convertir a JSON y enviar a Laravel
        print(df.to_json(orient='records'))

    except Exception as e:
        print(json.dumps({"error": str(e)}))

if __name__ == "__main__":
    solve()