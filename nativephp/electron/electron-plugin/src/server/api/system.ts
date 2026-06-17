import { BrowserWindow, nativeTheme, safeStorage, systemPreferences } from 'electron';
import express from 'express';
import { pathToFileURL } from 'url';

const router = express.Router();

const PDF_PRINT_RENDER_DELAY_MS = process.platform === 'win32' ? 2000 : 750;

function delay(ms: number): Promise<void> {
    return new Promise((resolve) => setTimeout(resolve, ms));
}

async function printPdfWithElectron(filePath: string, printer?: string, settings = {}): Promise<void> {
    let printWindow: BrowserWindow | null = new BrowserWindow({
        show: false,
        width: 800,
        height: 600,
        backgroundColor: '#FFFFFF',
    });

    const mergedSettings = {
        silent: true,
        printBackground: false,
        deviceName: printer,
        ...settings,
    };

    try {
        await printWindow.loadURL(pathToFileURL(filePath).toString());
        await delay(PDF_PRINT_RENDER_DELAY_MS);

        await new Promise<void>((resolve, reject) => {
            printWindow!.webContents.print(mergedSettings, (success, errorType) => {
                if (success) {
                    resolve();
                    return;
                }

                reject(new Error(errorType || 'Print failed'));
            });
        });
    } finally {
        if (printWindow) {
            printWindow.close();
            printWindow = null;
        }
    }
}

async function printPdfWithNativeWindows(filePath: string, printer?: string, settings = {}): Promise<void> {
    if (process.platform !== 'win32') {
        throw new Error('Native Windows printing is only available on Windows.');
    }

    const importer = new Function('specifier', 'return import(specifier)');
    const { PDFPrinter } = await importer('windows-pdf-printer-native');
    const pdfPrinter = printer ? new PDFPrinter(printer) : new PDFPrinter();

    await pdfPrinter.print(filePath, settings);
}

router.get('/can-prompt-touch-id', (req, res) => {
    res.json({
        result: systemPreferences.canPromptTouchID(),
    });
});

router.post('/prompt-touch-id', async (req, res) => {
    try {
        await systemPreferences.promptTouchID(req.body.reason);

        res.sendStatus(200);
    } catch (e) {
        res.status(400).json({
            error: e.message,
        });
    }
});

router.get('/can-encrypt', async (req, res) => {
    res.json({
        result: await safeStorage.isEncryptionAvailable(),
    });
});

router.post('/encrypt', async (req, res) => {
    try {
        res.json({
            result: await safeStorage.encryptString(req.body.string).toString('base64'),
        });
    } catch (e) {
        res.status(400).json({
            error: e.message,
        });
    }
});

router.post('/decrypt', async (req, res) => {
    try {
        res.json({
            result: await safeStorage.decryptString(Buffer.from(req.body.string, 'base64')),
        });
    } catch (e) {
        res.status(400).json({
            error: e.message,
        });
    }
});

router.get('/printers', async (req, res) => {
    const printers = await BrowserWindow.getAllWindows()[0].webContents.getPrintersAsync();

    res.json({
        printers,
    });
});

router.post('/print', async (req, res) => {
    const { printer, html, settings } = req.body;

    let printWindow = new BrowserWindow({
        show: false,
    });

    const defaultSettings = {
        silent: true,
        deviceName: printer,
    };

    const mergedSettings = {
        ...defaultSettings,
        ...(settings && typeof settings === 'object' ? settings : {}),
    };

    printWindow.webContents.on('did-finish-load', () => {
        printWindow.webContents.print(mergedSettings, (success, errorType) => {
            if (success) {
                console.log('Print job completed successfully.');
                res.sendStatus(200);
            } else {
                console.error('Print job failed:', errorType);
                res.sendStatus(500);
            }
            if (printWindow) {
                printWindow.close(); // Close the window and the process
                printWindow = null; // Free memory
            }
        });
    });

    await printWindow.loadURL(`data:text/html;charset=UTF-8,${html}`);
});

router.post('/print-file', async (req, res) => {
    const { filePath, printer, settings, copies = 1 } = req.body;

    if (!filePath || typeof filePath !== 'string') {
        res.status(422).json({
            error: 'filePath is required.',
        });
        return;
    }

    try {
        for (let copy = 0; copy < Math.max(1, Number(copies)); copy++) {
            await printPdfWithElectron(filePath, printer, settings);
        }

        res.sendStatus(200);
    } catch (e) {
        res.status(500).json({
            error: e.message,
        });
    }
});

router.post('/print-file-native-windows', async (req, res) => {
    const { filePath, printer, settings, copies = 1 } = req.body;

    if (!filePath || typeof filePath !== 'string') {
        res.status(422).json({
            error: 'filePath is required.',
        });
        return;
    }

    try {
        await printPdfWithNativeWindows(filePath, printer, {
            ...settings,
            copies: Math.max(1, Number(copies)),
        });

        res.sendStatus(200);
    } catch (e) {
        res.status(500).json({
            error: e.message,
        });
    }
});

router.post('/print-to-pdf', async (req, res) => {
    const { html, settings } = req.body;

    const printWindow = new BrowserWindow({
        show: false,
    });

    printWindow.webContents.on('did-finish-load', () => {
        printWindow.webContents
            .printToPDF(settings ?? {})
            .then((data) => {
                printWindow.close();
                res.json({
                    result: data.toString('base64'),
                });
            })
            .catch((e) => {
                printWindow.close();

                res.status(400).json({
                    error: e.message,
                });
            });
    });

    await printWindow.loadURL(`data:text/html;base64;charset=UTF-8,${html}`);
});

router.get('/theme', (req, res) => {
    res.json({
        result: nativeTheme.themeSource,
    });
});

router.post('/theme', (req, res) => {
    const { theme } = req.body;

    nativeTheme.themeSource = theme;

    res.json({
        result: theme,
    });
});

export default router;
