use windows::core::*;
use windows::Win32::Graphics::Direct3D::*;
use windows::Win32::Graphics::Direct3D11::*;
use windows::Win32::Graphics::Dxgi::{IDXGIDevice, IDXGIOutput1, IDXGIOutputDuplication, DXGI_OUTDUPL_FRAME_INFO};

pub struct DXGICapture {
    _device: ID3D11Device,
    _context: ID3D11DeviceContext,
    dupl: IDXGIOutputDuplication,
}

impl DXGICapture {
    pub fn new() -> Result<Self> {
        unsafe {
            let mut device = None;
            let mut context = None;
            
            D3D11CreateDevice(
                None,
                D3D_DRIVER_TYPE_HARDWARE,
                None,
                D3D11_CREATE_DEVICE_BGRA_SUPPORT,
                None,
                D3D11_SDK_VERSION,
                Some(&mut device),
                None,
                Some(&mut context),
            )?;

            let device = device.unwrap();
            let context = context.unwrap();

            let dxgi_device: IDXGIDevice = device.cast()?;
            let adapter = dxgi_device.GetAdapter()?;
            let output = adapter.EnumOutputs(0)?;
            let output1: IDXGIOutput1 = output.cast()?;

            let dupl = output1.DuplicateOutput(&device)?;

            Ok(Self { _device: device, _context: context, dupl })
        }
    }

    pub fn capture_frame(&self) -> Result<ID3D11Texture2D> {
        unsafe {
            let mut frame_info = DXGI_OUTDUPL_FRAME_INFO::default();
            let mut resource = None;

            // Release previous frame if any (handled by Duplication API naturally on next call usually)
            // But we must Acquire the first one
            self.dupl.AcquireNextFrame(100, &mut frame_info, &mut resource)?;

            let resource = resource.unwrap();
            let texture: ID3D11Texture2D = resource.cast()?;
            
            // Note: In real implementation, we would copy this to a staging texture
            // to access from CPU, or pass directly to Media Foundation Encoder on GPU.
            
            self.dupl.ReleaseFrame()?;
            
            Ok(texture)
        }
    }
}
