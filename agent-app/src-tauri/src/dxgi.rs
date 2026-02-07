use windows::core::*;
use windows::Win32::Graphics::Direct3D::*;
use windows::Win32::Graphics::Direct3D11::*;
use windows::Win32::Graphics::Dxgi::Common::{DXGI_FORMAT_B8G8R8A8_UNORM, DXGI_SAMPLE_DESC};
use windows::Win32::Graphics::Dxgi::{IDXGIDevice, IDXGIOutput1, IDXGIOutputDuplication, DXGI_OUTDUPL_FRAME_INFO, IDXGIAdapter};

pub struct CapturedFrame<'a> {
    pub texture: ID3D11Texture2D,
    dupl: &'a IDXGIOutputDuplication,
}

impl<'a> Drop for CapturedFrame<'a> {
    fn drop(&mut self) {
        unsafe {
            let _ = self.dupl.ReleaseFrame();
        }
    }
}

pub struct DXGICapture {
    pub _device: ID3D11Device,
    pub _context: ID3D11DeviceContext,
    dupl: IDXGIOutputDuplication,
    staging_texture: ID3D11Texture2D,
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

            let output_desc = output1.GetDesc()?;
            
            let width = (output_desc.DesktopCoordinates.right - output_desc.DesktopCoordinates.left).unsigned_abs();
            let height = (output_desc.DesktopCoordinates.bottom - output_desc.DesktopCoordinates.top).unsigned_abs();

            // Create Reusable Staging Texture
            let desc = D3D11_TEXTURE2D_DESC {
                Width: width,
                Height: height,
                MipLevels: 1,
                ArraySize: 1,
                Format: DXGI_FORMAT_B8G8R8A8_UNORM,
                SampleDesc: DXGI_SAMPLE_DESC { Count: 1, Quality: 0 },
                Usage: D3D11_USAGE_STAGING,
                BindFlags: 0,
                CPUAccessFlags: D3D11_CPU_ACCESS_READ.0 as u32,
                MiscFlags: 0,
            };

            let mut staging_texture = None;
            device.CreateTexture2D(&desc, None, Some(&mut staging_texture))?;
            let staging_texture = staging_texture.unwrap();

            Ok(Self { _device: device, _context: context, dupl, staging_texture })
        }
    }

    pub fn capture_frame(&self) -> Result<CapturedFrame<'_>> {
        unsafe {
            let mut frame_info = DXGI_OUTDUPL_FRAME_INFO::default();
            let mut resource = None;

            self.dupl.AcquireNextFrame(100, &mut frame_info, &mut resource)?;

            let resource = resource.unwrap();
            let texture: ID3D11Texture2D = resource.cast()?;
            
            Ok(CapturedFrame { texture, dupl: &self.dupl })
        }
    }

    pub fn get_texture_bytes(&self, texture: &ID3D11Texture2D) -> Result<Vec<u8>> {
        unsafe {
            let mut desc = D3D11_TEXTURE2D_DESC::default();
            texture.GetDesc(&mut desc);

            // Copy to staging
            self._context.CopyResource(&self.staging_texture, texture);

            let mut mapped = D3D11_MAPPED_SUBRESOURCE::default();
            self._context.Map(&self.staging_texture, 0, D3D11_MAP_READ, 0, Some(&mut mapped))?;

            let row_pitch = mapped.RowPitch as usize;
            let width = desc.Width as usize;
            let height = desc.Height as usize;
            
            // Optimization: avoid Vec::with_capacity and extend_from_slice loop if pitch matches.
            // But pitch usually doesn't match exactly.
            let mut bytes = vec![0u8; width * height * 4];
            let data = std::slice::from_raw_parts(mapped.pData as *const u8, row_pitch * height);
            
            for y in 0..height {
                let src_start = y * row_pitch;
                let src_end = src_start + width * 4;
                let dest_start = y * width * 4;
                let dest_end = dest_start + width * 4;
                bytes[dest_start..dest_end].copy_from_slice(&data[src_start..src_end]);
            }

            self._context.Unmap(&self.staging_texture, 0);
            Ok(bytes)
        }
    }
}
