use windows::Win32::Media::MediaFoundation::*;
use windows::Win32::Graphics::Direct3D11::*;
use windows::Win32::System::Com::*;
use windows::core::*;

pub struct MFEncoder {
    encoder: IMFTransform,
    _input_id: u32,
    _output_id: u32,
}

impl MFEncoder {
    pub fn new(width: u32, height: u32) -> Result<Self> {
        unsafe {
            MFStartup(MF_VERSION, MFSTARTUP_FULL)?;

            // 1. Find H.264 Encoder MFT
            let mut activate_ptr: *mut Option<IMFActivate> = std::ptr::null_mut();
            let mut count = 0;
            
            let mut attributes: Option<IMFAttributes> = None;
            MFCreateAttributes(&mut attributes, 1)?;
            let attributes = attributes.unwrap();
            attributes.SetGUID(&MF_TRANSFORM_CATEGORY_Attribute, &MFT_CATEGORY_VIDEO_ENCODER)?;

            MFTEnumEx(
                MFT_CATEGORY_VIDEO_ENCODER,
                MFT_ENUM_FLAG_HARDWARE | MFT_ENUM_FLAG_SORTANDFILTER,
                None,
                None,
                &mut activate_ptr,
                &mut count,
            )?;

            if count == 0 {
                return Err(Error::new(HRESULT(0x80040154u32 as i32), "H.264 Encoder not found"));
            }

            // In windows-rs, when we get a buffer from a C api like MFTEnumEx,
            // we should wrap it back into smart pointers.
            let activists = std::slice::from_raw_parts(activate_ptr, count as usize);
            let encoder: IMFTransform = activists[0].as_ref().unwrap().ActivateObject()?;
            
            // Re-wrap all pointers into smart pointers so they drop correctly
            for a in activists {
                let _ = a.as_ref().map(|p| p.clone()); 
            }
            CoTaskMemFree(Some(activate_ptr as _));

            // 2. Set Up Output Type (H.264)
            let output_type = MFCreateMediaType()?;
            output_type.SetGUID(&MF_MT_MAJOR_TYPE, &MFMediaType_Video)?;
            output_type.SetGUID(&MF_MT_SUBTYPE, &MFVideoFormat_H264)?;
            output_type.SetUINT32(&MF_MT_AVG_BITRATE, 2000000)?; 
            
            // Manual UINT64 pack for Size
            let pack_size = ((width as u64) << 32) | (height as u64);
            output_type.SetUINT64(&MF_MT_FRAME_SIZE, pack_size)?;
            
            // Manual UINT64 pack for Ratio (30/1)
            let pack_ratio = (30u64 << 32) | 1u64;
            output_type.SetUINT64(&MF_MT_FRAME_RATE, pack_ratio)?;
            
            output_type.SetUINT32(&MF_MT_INTERLACE_MODE, MFVideoInterlace_Progressive.0 as u32)?;

            encoder.SetOutputType(0, Some(&output_type), 0)?;

            // 3. Set Up Input Type (DirectX Texture BGRA)
            let input_type = MFCreateMediaType()?;
            input_type.SetGUID(&MF_MT_MAJOR_TYPE, &MFMediaType_Video)?;
            input_type.SetGUID(&MF_MT_SUBTYPE, &MFVideoFormat_RGB32)?; 
            input_type.SetUINT64(&MF_MT_FRAME_SIZE, pack_size)?;
            input_type.SetUINT64(&MF_MT_FRAME_RATE, pack_ratio)?;

            encoder.SetInputType(0, Some(&input_type), 0)?;

            encoder.ProcessMessage(MFT_MESSAGE_NOTIFY_BEGIN_STREAMING, 0)?;
            encoder.ProcessMessage(MFT_MESSAGE_NOTIFY_START_OF_STREAM, 0)?;

            Ok(Self {
                encoder,
                _input_id: 0,
                _output_id: 0,
            })
        }
    }

    pub fn encode_frame(&self, texture: &ID3D11Texture2D) -> Result<Vec<u8>> {
        unsafe {
            // 1. Create Media Buffer from Texture (Zero Copy)
            let buffer = MFCreateDXGISurfaceBuffer(&ID3D11Texture2D::IID, texture, 0, false)?;

            // 2. Create Sample
            let sample = MFCreateSample()?;
            sample.AddBuffer(&buffer)?;

            // 3. Process Input
            self.encoder.ProcessInput(0, Some(&sample), 0)?;

            // 4. Process Output
            let mut output_data = MFT_OUTPUT_DATA_BUFFER::default();
            output_data.dwStreamID = 0;

            let mut status = 0;
            let hres = self.encoder.ProcessOutput(0, &mut [output_data.clone()], &mut status);

            if hres.is_err() {
                // If it needs more input or is busy, return empty rather than erroring the whole loop
                return Ok(Vec::new());
            }

            // 5. Extract Bytes from Output Sample
            if let Some(out_sample) = &*output_data.pSample {
                let out_buffer = out_sample.GetBufferByIndex(0)?;

                let mut p_data: *mut u8 = std::ptr::null_mut();
                let mut current_len = 0;
                let mut max_len = 0;
                out_buffer.Lock(&mut p_data, Some(&mut max_len), Some(&mut current_len))?;

                let bytes = std::slice::from_raw_parts(p_data, current_len as usize).to_vec();
                out_buffer.Unlock()?;

                return Ok(bytes);
            }

            Ok(Vec::new())
        }
    }
}

impl Drop for MFEncoder {
    fn drop(&mut self) {
        unsafe {
            let _ = self.encoder.ProcessMessage(MFT_MESSAGE_NOTIFY_END_OF_STREAM, 0);
            let _ = self.encoder.ProcessMessage(MFT_MESSAGE_NOTIFY_END_STREAMING, 0);
            let _ = MFShutdown();
        }
    }
}
