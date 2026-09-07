"""
Input validation for UMKM data
"""

def validate_umkm_input(data):
    """
    Validate single UMKM input data
    Returns: (is_valid, errors)
    """
    errors = []
    
    # Required numeric fields
    numeric_fields = [
        'aset', 'bangunan_gedung', 'jumlah_investasi', 'jumlah_tenaga_kerja',
        'lain_lain', 'mesin_peralatan', 'mesin_peralatan_impor', 'modal_kerja',
        'omzet', 'pembelian_pematangan_tanah', 'tki'
    ]
    
    # Required categorical fields
    categorical_fields = [
        'jenis_perusahaan', 'risiko_proyek', 'skala_usaha',
        'status_penanaman_modal', 'kecamatan_usaha', 'kelurahan_usaha',
        'kl_sektor_pembina', 'judul_kbli'
    ]
    
    # Check numeric fields
    for field in numeric_fields:
        if field not in data:
            errors.append(f"Missing required field: {field}")
        else:
            try:
                value = float(data[field])
                if value < 0:
                    errors.append(f"Field {field} must be non-negative")
            except (ValueError, TypeError):
                errors.append(f"Field {field} must be numeric")
    
    # Check categorical fields (optional but recommended)
    for field in categorical_fields:
        if field not in data:
            # Will be filled with 'Unknown' in preprocessing
            pass
        else:
            if not isinstance(data[field], str):
                errors.append(f"Field {field} must be string")
    
    return (len(errors) == 0, errors)

def validate_batch_input(filepath):
    """
    Validate batch input file
    Returns: (is_valid, errors)
    """
    import pandas as pd
    
    errors = []
    
    try:
        # Load file
        if filepath.endswith('.csv'):
            df = pd.read_csv(filepath)
        else:
            df = pd.read_excel(filepath)
        
        # Check if file is empty
        if len(df) == 0:
            errors.append("File is empty")
            return (False, errors)
        
        # Check required columns
        numeric_fields = [
            'aset', 'bangunan_gedung', 'jumlah_investasi', 'jumlah_tenaga_kerja',
            'lain_lain', 'mesin_peralatan', 'mesin_peralatan_impor', 'modal_kerja',
            'omzet', 'pembelian_pematangan_tanah', 'tki'
        ]
        
        missing_cols = [col for col in numeric_fields if col not in df.columns]
        if missing_cols:
            errors.append(f"Missing required columns: {', '.join(missing_cols)}")
        
        # Check if at least some rows have valid data
        valid_rows = 0
        for col in numeric_fields:
            if col in df.columns:
                valid_rows = len(df[df[col].notna()])
                break
        
        if valid_rows == 0:
            errors.append("No valid data rows found")
        
    except Exception as e:
        errors.append(f"Error reading file: {str(e)}")
    
    return (len(errors) == 0, errors)
