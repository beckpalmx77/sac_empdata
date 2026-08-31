/**
 * HEIC to JPG converter helper for SAC EmpData
 */
function isHeicFile(file) {
    if (!file || !file.name) return false;
    const ext = file.name.split('.').pop().toLowerCase();
    const mime = (file.type || '').toLowerCase();
    return ext === 'heic' || ext === 'heif' || mime === 'image/heic' || mime === 'image/heif';
}

async function convertHeicToJpg(file) {
    if (!isHeicFile(file)) {
        return file;
    }
    if (typeof heic2any === 'undefined') {
        console.warn('heic2any library is not loaded');
        return file;
    }
    try {
        const result = await heic2any({
            blob: file,
            toType: 'image/jpeg',
            quality: 0.85
        });
        const blob = Array.isArray(result) ? result[0] : result;
        let newName = file.name.replace(/\.(heic|heif)$/i, '.jpg');
        if (!newName.toLowerCase().endsWith('.jpg') && !newName.toLowerCase().endsWith('.jpeg')) {
            newName += '.jpg';
        }
        return new File([blob], newName, {
            type: 'image/jpeg',
            lastModified: Date.now()
        });
    } catch (err) {
        console.error('HEIC conversion failed for ' + file.name + ':', err);
        return file;
    }
}

async function convertFileList(files) {
    const converted = [];
    let hasHeic = false;
    for (const file of files) {
        if (isHeicFile(file)) {
            hasHeic = true;
            break;
        }
    }
    if (hasHeic) {
        if (typeof alertify !== 'undefined' && alertify.message) {
            alertify.message('กำลังแปลงไฟล์ HEIC เป็น JPG...');
        }
    }
    for (const file of files) {
        if (isHeicFile(file)) {
            const convertedFile = await convertHeicToJpg(file);
            converted.push(convertedFile);
        } else {
            converted.push(file);
        }
    }
    return converted;
}
